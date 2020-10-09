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

use froq\App;
use froq\util\Util;
use froq\http\MessageException;
use froq\http\message\{Body, Cookies, Headers};
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
     * @var froq\App
     */
    protected App $app;

    /**
     * Type.
     * @var int
     */
    protected int $type;

    /**
     * HTTP Version.
     * @var string
     */
    protected string $httpVersion;

    /**
     * Headers.
     * @var froq\http\message\Headers
     */
    protected Headers $headers;

    /**
     * Cookies.
     * @var froq\http\message\Cookies
     */
    protected Cookies $cookies;

    /**
     * Body.
     * @var froq\http\message\Body
     */
    protected Body $body;

    /**
     * Constructor.
     * @param froq\App $app
     * @param int      $type
     */
    public function __construct(App $app, int $type)
    {
        $this->app         = $app;
        $this->type        = $type;
        $this->httpVersion = Http::version();

        $this->headers     = new Headers();
        $this->cookies     = new Cookies();
        $this->body        = new Body();
    }

    /**
     * Get app.
     * @return froq\App
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
     * Get http version number.
     * @return float
     * @since  4.7
     */
    public final function getHttpVersionNumber(): float
    {
        return (float) substr($this->httpVersion, 5, 3);
    }

    /**
     * Set/get headers.
     * @param  ...$arguments
     * @return self|froq\http\message\Headers
     */
    public final function headers(...$arguments)
    {
        if ($arguments) {
            if ($this->isRequest()) {
                throw new MessageException('Connot modify request headers');
            }

            return $this->setHeaders(...$arguments);
        }

        return $this->headers;
    }

    /**
     * Set/get cookies.
     * @param  ...$arguments
     * @return self|froq\http\message\Cookies
     * @throws froq\http\MessageException
     */
    public final function cookies(...$arguments)
    {
        if ($arguments) {
            if ($this->isRequest()) {
                throw new MessageException('Connot modify request cookies');
            }

            return $this->setCookies(...$arguments);
        }

        return $this->cookies;
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
     * @param  array<string, any> $headers
     * @return self
     * @since  4.0
     */
    public final function addHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set headers.
     * @param  array<string, any> $headers
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
     * Set cookies.
     * @param  array<string, any> $cookies
     * @return self
     */
    public function setCookies(array $cookies): self
    {
        foreach ($cookies as $name => $value) {
            $this->setCookie($name, $value);
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
     * Get cookies.
     * @return froq\http\message\Cookies
     * @since  4.0
     */
    public final function getCookies(): Cookies
    {
        return $this->cookies;
    }

    /**
     * Set body.
     * @param  any|null   $content
     * @param  array|null $contentAttributes
     * @param  bool|null  $isError @internal
     * @return self
     * @throws froq\http\MessageException
     */
    public final function setBody($content, array $contentAttributes = null, bool $isError = null): self
    {
        // @cancel
        // $isError is an internal option and string content needed here.
        // @see App.error() and App.endOutputBuffer().
        // if (!$isError) {
        //     $this->body->setContent($content)
        //                ->setContentAttributes($contentAttributes);
        //     return $this;
        // }

        if ($this->isRequest()) {
            if ($content != null) {
                $this->body->setContent($content)
                           ->setContentAttributes($contentAttributes);
            }
        }
        elseif ($this->isResponse()) {
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
                        throw new MessageException('Missing content type for "%s" type content value',
                            [$contentValueType]);
                    }
                    if (!preg_match('~(json|xml)~', $contentType)) {
                        throw new MessageException('Invalid content value type for "%s" type content, '.
                            'content type must be such type "xxx/json" or "xxx/xml"', [$contentValueType]);
                    }
                } elseif ($contentValueType != 'null' && $contentValueType != 'scalar') {
                    throw new MessageException('Invalid content value type "%s"', [$contentValueType]);
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
