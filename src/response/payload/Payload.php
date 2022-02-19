<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{PayloadInterface, PayloadException,
    JsonPayload, XmlPayload, FilePayload, ImagePayload};
use froq\http\{Response, response\Status};
use froq\common\trait\AttributeTrait;
use froq\file\mime\Mime;
use froq\util\Util;

/**
 * Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\Payload
 * @author  Kerem Güneş
 * @since   4.0
 */
class Payload
{
    /** @see froq\common\trait\AttributeTrait */
    use AttributeTrait;

    /** @var mixed|null */
    protected mixed $content = null;

    /** @var froq\http\Response|null */
    protected Response|null $response = null;

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
     * @return mixed|null
     */
    public final function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Get content type.
     *
     * @return string|null
     * @since  6.0
     */
    public final function getContentType(): string|null
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
    public final function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Get owner response.
     *
     * @return froq\http\Response|null
     * @internal
     */
    public final function getResponse(): Response|null
    {
        return $this->response;
    }

    /**
     * Get response code attribute.
     *
     * @return int
     */
    public final function getResponseCode(): int
    {
        return $this->getAttribute('code');
    }

    /**
     * Get response headers attribute.
     *
     * @return array
     */
    public final function getResponseHeaders(): array
    {
        return $this->getAttribute('headers', []);
    }
    /**
     * Get response cookies attribute.
     *
     * @return array
     */
    public final function getResponseCookies(): array
    {
        return $this->getAttribute('cookies', []);
    }

    /**
     * Process.
     *
     * Detect payload content type, processes over and return an array which contains content,
     * content attributes (mime, size or filename etc.) and response attributes (code, headers,
     * cookies).
     *
     * @param  froq\http\Response $response
     * @return array
     */
    public final function process(Response $response): array
    {
        $payload = $this;
        $payload->setResponse($response);

        // Check for not-modified status.
        if ($payload->getContent() == null
            && $payload->getResponseCode() == Status::NOT_MODIFIED) {
            // Return content, content attributes, response attributes.
            return [
                $content = null,
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
                    'Failed to achive string/resource content from payload %s',
                    get_class($payload)
                );
            }
        }
        // Not ready to handle, try to create (eg: Payload).
        else {
            $contentType = $payload->getContentType();
            if ($contentType == null) {
                throw new PayloadException('Content type must not be empty');
            }

            // Detect content type and process.
            switch ($type = self::sniffContentType($contentType)) {
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
                    $payload = self::createPayload($type, [
                        $payload->getResponseCode(), $payload->getContent(),
                        $payload->getAttributes(), $response
                    ]);

                    $content = $payload->handle();
                    if (!is_null($content) && !is_string($content)) {
                        throw new PayloadException(
                            'Failed getting string content from payload %s, [return: %s]',
                            [get_class($payload), get_type($content)]
                        );
                    }
                    break;
                case 'image': case 'file': case 'download':
                    $payload = self::createPayload($type, [
                        $payload->getResponseCode(), $payload->getContent(),
                        $payload->getAttributes(), $response
                    ]);

                    $content = $payload->handle();
                    if (!is_image($content) && !is_stream($content)
                        // Skip direct file reads.
                        && !$payload->getAttribute('direct')
                    ) {
                        throw new PayloadException(
                            'Failed getting resource content from payload %s, [return: %s]',
                            [get_class($payload), get_type($content)]
                        );
                    }
                    break;
                default:
                    throw new PayloadException(
                        'Invalid payload type `%s`',
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
     *
     * @param  string $file
     * @param  mixed  $option
     * @return int|string|null
     * @since  5.0
     */
    protected static function getModifiedAt(string $file, mixed $option): int|string|null
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
     * Get "memory limit" directive as converted.
     *
     * @param  string|null &$limit
     * @return int
     * @since  5.0
     */
    protected static function getMemoryLimit(string &$limit = null): int
    {
        $limit = (string) ini_get('memory_limit');

        return Util::convertBytes($limit);
    }

    /**
     * Sniff given content type and return a pseudo type if valid.
     */
    private static function sniffContentType(string $contentType): string|null
    {
        $contentType = strtolower($contentType);
        if ($contentType == 'n/a') {
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
    private static function createPayload(string $type, array $args): PayloadInterface
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
