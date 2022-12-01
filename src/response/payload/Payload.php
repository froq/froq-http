<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http\response\payload;

use froq\http\Response;
use froq\common\trait\AttributeTrait;
use froq\file\mime\Mime;
use froq\util\Util;

/**
 * Base payload class extended by other payload classes.
 *
 * @package froq\http\response\payload
 * @class   froq\http\response\payload\Payload
 * @author  Kerem Güneş
 * @since   4.0
 */
class Payload
{
    use AttributeTrait;

    /** Payload content. */
    protected mixed $content;

    /** Response instance. */
    protected Response|null $response;

    /**
     * Constructor.
     *
     * @param int                     $code
     * @param mixed|null              $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, mixed $content = null, array $attributes = null, Response $response = null)
    {
        $this->content      = $content;
        $this->response     = $response;

        $attributes['code'] = $code;

        $this->setAttributes($attributes);
    }

    /**
     * Get content.
     *
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Get content type.
     *
     * @return string|null
     * @since  6.0
     */
    public function getContentType(): string|null
    {
        return $this->getAttribute('type');
    }

    /**
     * Set owner response.
     *
     * @param  froq\http\Response $response
     * @return void
     * @internal
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Get owner response.
     *
     * @return froq\http\Response|null
     * @internal
     */
    public function getResponse(): Response|null
    {
        return $this->response;
    }

    /**
     * Get response code attribute.
     *
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->getAttribute('code');
    }

    /**
     * Get response headers attribute.
     *
     * @return array
     */
    public function getResponseHeaders(): array
    {
        return $this->getAttribute('headers', []);
    }

    /**
     * Get response cookies attribute.
     *
     * @return array
     */
    public function getResponseCookies(): array
    {
        return $this->getAttribute('cookies', []);
    }

    /**
     * Detect payload content type, processes over and return an array which contains content,
     * content attributes (mime, size or filename etc.) and response attributes (code, headers,
     * cookies).
     *
     * @param  froq\http\Response $response
     * @return array
     */
    public function process(Response $response): array
    {
        $payload = $this;
        $payload->setResponse($response);

        // Check non-body stuff.
        if (!$response->allowsBody()) {
            // Return content, content attributes, response attributes.
            return [
                null,
                $payload->getAttributes(),
                [$payload->getResponseCode(),
                 $payload->getResponseHeaders(),
                 $payload->getResponseCookies()]
            ];
        }

        // Ready to handle (eg: JsonPayload, XmlPayload etc).
        if ($payload instanceof PayloadInterface) {
            $content = $payload->handle();
            if (!is_null($content) && !is_string($content)
                && !is_image($content) && !is_stream($content)) {
                throw new PayloadException(
                    'Failed to achive string|image|stream|null content from payload %s',
                    get_class($payload)
                );
            }
        }
        // Not ready to handle, try to create (eg: Payload).
        else {
            $contentType = $payload->getContentType()
                ?: throw new PayloadException('Content type must not be empty');

            // Detect content type and process.
            switch ($type = $this->sniffContentType($contentType)) {
                case 'n/a':
                    $content = null;
                    break;
                case 'text':
                    $content = $payload->getContent();
                    if (!is_null($content) && !is_string($content)) {
                        throw new PayloadException(
                            'Content must be string|null for text responses, %s given',
                            get_type($content)
                        );
                    }
                    break;
                case 'json': case 'xml':
                    $payload = $this->createPayload($type, [
                        $payload->getResponseCode(), $payload->getContent(),
                        $payload->getAttributes(), $response
                    ]);

                    $content = $payload->handle();
                    if (!is_null($content) && !is_string($content)) {
                        throw new PayloadException(
                            'Failed getting string|null content from payload %s [return: %s]',
                            [get_class($payload), get_type($content)]
                        );
                    }
                    break;
                case 'image': case 'file': case 'download':
                    $payload = $this->createPayload($type, [
                        $payload->getResponseCode(), $payload->getContent(),
                        $payload->getAttributes(), $response
                    ]);

                    $content = $payload->handle();
                    if (!is_image($content) && !is_stream($content)
                        && !$payload->getAttribute('direct') // Skip direct image/file reads.
                    ) {
                        throw new PayloadException(
                            'Failed getting image|stream content from payload %s [return: %s]',
                            [get_class($payload), get_type($content)]
                        );
                    }
                    break;
                default:
                    throw new PayloadException(
                        'Invalid payload type %q',
                        $type ?? $payload->getContentType()
                    );
            }
        }

        // Return content, content attributes, response attributes.
        return [
            $content,
            $payload->getAttributes(),
            [$payload->getResponseCode(),
             $payload->getResponseHeaders(),
             $payload->getResponseCookies()]
        ];
    }

    /**
     * Get "modified at" option as timestamp.
     */
    protected function getModifiedAt(string $file, mixed $option): int|string|null
    {
        // Disable directive.
        if ($option === null || $option === false) {
            return null;
        }
        // Now directive.
        if ($option === 0) {
            return time();
        }
        // When manually given.
        if (is_int($option) || is_string($option)) {
            return $option;
        }

        return $file ? filemtime($file) : null;
    }

    /**
     * Get "memory limit" directive as converted bytes.
     */
    protected function getMemoryLimit(string &$limit = null): int
    {
        $limit = (string) ini_get('memory_limit');

        return Util::convertBytes($limit);
    }

    /**
     * Sniff given content type and return a pseudo type if valid.
     */
    private function sniffContentType(string $contentType): string|null
    {
        $contentType = strtolower($contentType);
        if ($contentType === 'n/a') {
            return 'n/a';
        }

        // Eg: text/html, image/jpeg, application/json.
        if (preg_match('~/(?:.*?(\w+)$)?~', $contentType, $match)) {
            $match = match ($match[1]) {
                'json' => 'json', 'xml' => 'xml',
                'jpeg', 'webp', 'png', 'gif' => 'image',
                'html', 'plain', 'css', 'javascript' => 'text',
                'octet-stream' => 'file',
                default => null,
            };

            // Any matches above.
            if ($match) {
                return $match;
            }

            // Any type of those trivials download, x-download, force-download etc.
            if (str_ends_with($contentType, 'download')) {
                return 'download';
            }

            // Any extension with a valid type.
            if (Mime::getExtensionByType($contentType)) {
                return 'download';
            }
        }

        return null; // Invalid.
    }

    /**
     * Create a payload object by given pseudo type.
     */
    private function createPayload(string $type, array $args): PayloadInterface
    {
        switch ($type) {
            case 'json':
                return new JsonPayload(...$args);
            case 'xml':
                return new XmlPayload(...$args);
            case 'image':
                return new ImagePayload(...$args);
            case 'file':
            case 'download':
                return new FilePayload(...$args);
        };
    }
}
