<?php
/**
 * MIT License <https://opensource.org/licenses/mit>
 *
 * Copyright (c) 2015 Kerem Güneş
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\common\traits\AttributeTrait;
use froq\file\Mime;
use froq\http\Response as Container;
use froq\http\response\payload\{PayloadInterface, JsonPayload, XmlPayload, FilePayload, ImagePayload};

/**
 * Payload.
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
     * Code.
     * @var int
     */
    protected int $code;

    /**
     * Content.
     * @var any
     */
    protected $content;

    /**
     * Container.
     * @var froq\http\Response
     * @internal
     */
    protected ?Container $container;

    /**
     * Constructor.
     * @param int                     $code
     * @param any                     $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $container
     */
    public function __construct(int $code, $content, array $attributes = null,
        Container $container = null)
    {
        $this->code      = $code;
        $this->content   = $content;
        $this->container = $container;

        $this->setAttributes($attributes ?? []);
    }

    /**
     * Get code.
     * @return int
     */
    public final function getCode(): int
    {
        return $this->code;
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
     * Get headers.
     * @return array
     */
    public final function getHeaders(): array
    {
        return $this->getAttribute('headers', []);
    }
    /**
     * Get cookies.
     * @return array
     */
    public final function getCookies(): array
    {
        return $this->getAttribute('cookies', []);
    }

    /**
     * Set container.
     * @param  froq\http\Response $container
     * @return void
     * @internal
     */
    public final function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Set container.
     * @return ?froq\http\Response
     * @internal
     */
    public final function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * Process.
     *
     * Detects payload content type, processes over and returns array that contains content,
     * content attributes (mime, size or filename etc.) and response attributes (code, headers,
     * cookies).
     *
     * @param  froq\http\Response $container
     * @return array<string|resource, array, array>
     */
    public final function process(Container $container): array
    {
        $payload = $this;
        $payload->setContainer($container);

        if ($this instanceof PayloadInterface) {
            $content = $payload->handle();
            if (!is_string($content) && !is_resource($content)) {
                throw new PayloadException('Failed to achive resource content from "%s"',
                    [get_class($this)]);
            }
        } else {
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
                        throw new PayloadException('Content must be null or string for text '.
                            'responses');
                    }
                    break;
                case 'json': case 'xml':
                    $payload = self::createPayload($type, $payload->getCode(), $payload->getContent(),
                        $payload->getAttributes(), $container);

                    $content = $payload->handle();
                    if (!is_string($content)) {
                        throw new PayloadException('Failed to achive string content from "%s"',
                            [get_class($payload)]);
                    }
                    break;
                case 'image': case 'file': case 'download':
                    $payload = self::createPayload($type, $payload->getCode(), $payload->getContent(),
                        $payload->getAttributes(), $container);

                    $content = $payload->handle();
                    if (!is_resource($content)) {
                        throw new PayloadException('Failed to achive resource content from "%s"',
                            [get_class($payload)]);
                    }
                    break;
                default:
                    throw new PayloadException('Invalid payload content type "%s"',
                        [$type ?? $payload->getAttribute('type')]);
            }
        }

        // Return content, content attributes, response attributes.
        return [
            $content,
            $payload->getAttributes(),
            [$payload->getCode(), $payload->getHeaders(), $payload->getCookies()]
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
                case 'jpeg': case 'png': case 'gif':
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
     * @param  ...    $arguments
     * @return froq\http\response\payload\PayloadInterface
     */
    private static final function createPayload(string $type, ...$arguments): PayloadInterface
    {
        switch ($type) {
            case 'json':
                return new JsonPayload(...$arguments);
            case 'xml':
                return new XmlPayload(...$arguments);
            case 'image':
                return new ImagePayload(...$arguments);
            case 'file': case 'download':
                return new FilePayload(...$arguments);
        }
    }
}
