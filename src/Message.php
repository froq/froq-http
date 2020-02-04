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

namespace froq\http;

use froq\app\App;
use froq\util\Util;
use froq\http\HttpException;
use froq\http\message\{Headers, Body};
use froq\http\response\payload\Payload;

/**
 * Message.
 * @package froq\http
 * @object  froq\http\Message
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
abstract class Message
{
    /**
     * Types.
     * @const int
     */
    public const TYPE_REQUEST  = 1,
                 TYPE_RESPONSE = 2;

    /**
     * App.
     * @var froq\app\App
     */
    protected $app;

    /**
     * Type.
     * @var int
     */
    protected $type;

    /**
     * HTTP Version.
     * @var string
     */
    protected $httpVersion;

    /**
     * Headers.
     * @var froq\http\message\Headers
     */
    protected $headers;

    /**
     * Body.
     * @var froq\http\message\Body
     */
    protected $body;

    /**
     * Constructor.
     * @param froq\app\App $app
     * @param int          $type
     */
    public function __construct(App $app, int $type)
    {
        $this->app = $app;
        $this->type = $type;
        $this->httpVersion = Http::detectVersion();

        $this->headers = new Headers();
        $this->body = new Body();
    }

    /**
     * Get app.
     * @return froq\app\App
     */
    public final function getApp(): App
    {
        return $this->app;
    }

    /**
     * Get type.
     * @return int
     */
    public final function getType(): int
    {
        return $this->type;
    }

    /**
     * Get http version.
     * @return string
     */
    public final function getHttpVersion(): string
    {
        return $this->httpVersion;
    }

    /**
     * Set/get headers.
     * @param  ...$arguments
     * @return self|froq\http\message\Headers
     */
    public final function headers(...$arguments)
    {
        return $arguments ? $this->setHeaders(...$arguments) : $this->headers;
    }

    /**
     * Set/get body.
     * @param  ...$arguments
     * @return self|froq\http\message\Body
     */
    public final function body(...$arguments)
    {
        return $arguments ? $this->setBody(...$arguments) : $this->body;
    }

    /**
     * Set headers.
     * @param  array $headers
     * @return self
     */
    public final function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Get headers.
     * @return froq\http\message\Headers
     */
    public final function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * Set body.
     * @param  any|null   $content
     * @param  array|null $contentAttributes
     * @param  bool|null  $isError @internal
     * @return self
     * @throws froq\http\HttpException
     */
    public final function setBody($content, array $contentAttributes = null, bool $isError = null): self
    {
        // $isError is an internal option and string content needed here.
        // @see App.error() and App.endOutputBuffer().
        if ($isError) {
            $this->body->setContent($content)
                       ->setContentAttributes($contentAttributes);
        } elseif ($this->isRequest()) {
            if ($content != null) {
                $this->body->setContent($content)
                           ->setContentAttributes($contentAttributes);
            }
        } elseif ($this->isResponse()) {
            // Payload contents.
            if ($content instanceof Payload) {
                $payload = $content;
            }
            // Text contents.
            elseif (is_string($content)) {
                // Prepare defaults with type.
                $contentAttributes = array_merge([
                    'type' => $this->getContentType() ?? Body::CONTENT_TYPE_TEXT_HTML,
                ], (array) $contentAttributes);

                $payload = new Payload($this->getStatusCode(), $content, $contentAttributes);
            }
            // File/image contents.
            elseif (is_resource($content)) {
                $payload = new Payload($this->getStatusCode(), $content, $contentAttributes);
            }
            // All others.
            else {
                // Prepare defaults with type.
                $contentAttributes = array_merge([
                    'type' => $this->getContentType() ?? Body::CONTENT_TYPE_TEXT_HTML,
                ], (array) $contentAttributes);

                $contentType = (string) ($contentAttributes['type'] ?? '');
                $contentValueType = Util::getType($content, true, true);

                if ($contentValueType == 'array' || $contentValueType == 'object') {
                    if ($contentType == '') {
                        throw new HttpException(sprintf('Missing content type for %s type '.
                            'content value', $contentValueType));
                    }
                    if (!preg_match('~(json|xml)~', $contentType)) {
                        throw new HttpException(sprintf('Invalid content value type for %s type '.
                            'content, content type must be such type "xxx/json" or "xxx/xml"',
                            $contentValueType));
                    }
                } elseif ($contentValueType != 'null' && $contentValueType != 'scalar') {
                    throw new HttpException(sprintf('Invalid content value type %s',
                        $contentValueType));
                }

                $payload = new Payload($this->getStatusCode(), $content, $contentAttributes);
            }

            // @override
            [$content, $contentAttributes, $responseAttributes] = $payload->process($this);

            // Set original arguments or their overrides, finally..
            $this->body->setContent($content)
                       ->setContentAttributes($contentAttributes);

            if (isset($responseAttributes)) {
                @ [$code, $headers, $cookies] = $responseAttributes;

                $code    && $this->setStatus($code);
                $headers && $this->setHeaders($headers);
                $cookies && $this->setCookies($cookies);
            }
        }

        return $this;
    }

    /**
     * Get body.
     * @return froq\http\message\Body
     */
    public final function getBody(): Body
    {
        return $this->body;
    }

    /**
     * Is request.
     * @return bool
     */
    public final function isRequest(): bool
    {
        return ($this->type == self::TYPE_REQUEST);
    }

    /**
     * Is response.
     * @return bool
     */
    public final function isResponse(): bool
    {
        return ($this->type == self::TYPE_RESPONSE);
    }
}
