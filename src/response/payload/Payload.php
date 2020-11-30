<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{PayloadInterface, PayloadException,
    JsonPayload, XmlPayload, FilePayload, ImagePayload};
use froq\http\{Response, response\Status};
use froq\file\mime\Mime;
use froq\common\traits\AttributeTrait;

/**
 * Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\Payload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 */
class Payload
{
    /**
     * Attribute trait.
     *
     * @see froq\common\traits\AttributeTrait
     */
    use AttributeTrait;

    /**
     * Content.
     * @var any
     */
    protected $content;

    /**
     * Response.
     * @var froq\http\Response
     * @internal
     */
    protected ?Response $response;

    /**
     * Constructor.
     * @param int                     $code
     * @param any                     $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null,
        Response $response = null)
    {
        $this->content  = $content;
        $this->response = $response;

        $attributes['code'] = $code;

        $this->setAttributes($attributes);
    }

    /**
     * Get content.
     * @return any
     */
    public final function getContent()
    {
        return $this->content;
    }

    /**
     * Set response.
     * @param  froq\http\Response $response
     * @return void
     * @internal
     */
    public final function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Set response.
     * @return ?froq\http\Response
     * @internal
     */
    public final function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get response code.
     * @return int
     */
    public final function getResponseCode(): int
    {
        return $this->getAttribute('code');
    }

    /**
     * Get response headers.
     * @return array
     */
    public final function getResponseHeaders(): array
    {
        return $this->getAttribute('headers', []);
    }
    /**
     * Get response cookies.
     * @return array
     */
    public final function getResponseCookies(): array
    {
        return $this->getAttribute('cookies', []);
    }

    /**
     * Process.
     *
     * Detects payload content type, processes over and returns array that contains content,
     * content attributes (mime, size or filename etc.) and response attributes (code, headers,
     * cookies).
     *
     * @param  froq\http\Response $response
     * @return array<string|resource, array, array>
     */
    public final function process(Response $response): array
    {
        $payload = $this;
        $payload->setResponse($response);

        // Check for not-modified status.
        if ($payload->getContent() == null && $payload->getResponseCode() == Status::NOT_MODIFIED) {
            // Return content, content attributes, response attributes.
            return [
                null,
                $payload->getAttributes(),
                [$payload->getResponseCode(), $payload->getResponseHeaders(), $payload->getResponseCookies()]
            ];
        }

        // Ready to handle (eg: JsonPayload, XmlPayload etc).
        if ($payload instanceof PayloadInterface) {
            $content = $payload->handle();
            if (!is_null($content) && !is_string($content) && !is_resource($content)) {
                throw new PayloadException("Failed to achive string/resource content from '%s' payload object",
                    get_class($payload));
            }
        }
        // Not ready to handle, try to create (eg: Payload).
        else {
            $contentType = $payload->getAttribute('type');
            if ($contentType == null) {
                throw new PayloadException('Content type must not be empty');
            }

            // Detect content type and process.
            $type = self::sniffContentType($contentType);
            switch ($type) {
                case 'n/a':
                    $content = '';
                    break;
                case 'text':
                    $content = $payload->getContent();
                    if (!is_null($content) && !is_string($content)) {
                        throw new PayloadException("Content must be string|null for text responses, '%s' given",
                            gettype($content));
                    }
                    break;
                case 'json': case 'xml':
                    $payload = self::createPayload($type, $payload->getResponseCode(),
                        $payload->getContent(), $payload->getAttributes(), $response);

                    $content = $payload->handle();
                    if (!is_null($content) && !is_string($content)) {
                        throw new PayloadException("Failed to achive string content from '%s'", get_class($payload));
                    }
                    break;
                case 'image': case 'file': case 'download':
                    $payload = self::createPayload($type, $payload->getResponseCode(),
                        $payload->getContent(), $payload->getAttributes(), $response);

                    $content = $payload->handle();
                    if (!is_resource($content)) {
                        throw new PayloadException("Failed to achive resource content from '%s'", get_class($payload));
                    }
                    break;
                default:
                    throw new PayloadException("Invalid payload content type '%s'", $type ?? $payload->getAttribute('type'));
            }
        }

        // Return content, content attributes, response attributes.
        return [
            $content,
            $payload->getAttributes(),
            [$payload->getResponseCode(), $payload->getResponseHeaders(), $payload->getResponseCookies()]
        ];
    }

    /**
     * Sniff content type.
     * @param  ?string $contentType
     * @return ?string
     */
    private static function sniffContentType(?string $contentType): ?string
    {
        $contentType = (string) $contentType;
        if ($contentType == 'n/a') {
            return 'n/a';
        }

        // Eg: text/html, image/jpeg, application/json.
        if (preg_match('~/(?:.*?(\w+)$)?~i', $contentType, $match)) {
            switch ($match[1]) {
                case 'html': case 'plain':
                case 'javascript': case 'css':
                    return 'text';
                case 'json':
                    return 'json';
                case 'xml':
                    return 'xml';
                case 'jpeg': case 'webp':
                case 'png': case 'gif':
                    return 'image';
                case 'octet-stream':
                    return 'file';
            }

            // Any type of those trivials download, x-download, force-download etc.
            if (substr($contentType, -8) == 'download') {
                return 'download';
            }

            // Any extension with a valid type.
            $extension = Mime::getExtensionByType($contentType);
            if ($extension != null) {
                return 'download';
            }
        }

        // Invalid content type.
        return null;
    }

    /**
     * Create payload.
     * @param  string $type
     * @param  ...    $args
     * @return froq\http\response\payload\PayloadInterface
     */
    private static function createPayload(string $type, ...$args): PayloadInterface
    {
        switch ($type) {
            case 'json':
                return new JsonPayload(...$args);
            case 'xml':
                return new XmlPayload(...$args);
            case 'image':
                return new ImagePayload(...$args);
            case 'file': case 'download':
                return new FilePayload(...$args);
        }
    }
}
