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
use froq\encoding\{Encoder, EncoderException};
use froq\http\response\{Status, Body, Response as ReturnResponse};

/**
 * Response.
 * @package froq\http
 * @object  froq\http\Response
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Response extends Message
{
    /**
     * Status.
     * @var froq\http\response\Status
     */
    private $status;

    /**
     * Body.
     * @var froq\http\response\Body
     */
    private $body;

    /**
     * Constructor.
     * @param froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app, parent::TYPE_RESPONSE);

        $this->status = new Status();
        $this->body = new Body();

        $this->setHeaders($this->app->configValue('headers', []));
        $this->setCookies($this->app->configValue('cookies', []));
    }

    /**
     * Call magic (proxify body methods).
     * @param  string $method
     * @param  array  $methodArguments
     * @return any
     * @throws froq\http\HttpException
     */
    public function __call(string $method, array $methodArguments)
    {
        if (method_exists($this->body, $method)) {
            // proxify body methods
            return call_user_func_array([$this->body, $method], $methodArguments);
        }

        throw new HttpException("Call to undefined method '{$method}'");
    }

    /**
     * Get status.
     * @return froq\http\response\Status
     */
    public function status(): Status
    {
        return $this->status;
    }

    /**
     * Redirect.
     * @param  string $location
     * @param  int    $code
     * @return self
     */
    public function redirect(string $location, int $code = Status::FOUND): self
    {
        $this->setStatus($code)->setHeader('Location', trim($location));

        return $this;
    }

    /**
     * Set status.
     * @param  int    $code
     * @param  string $text
     * @return self
     */
    public function setStatus(int $code, string $text = null): self
    {
        if ($text == null) {
            $text = Status::getTextByCode($code);
        }

        $this->status->setCode($code);
        $this->status->setText($text);

        return $this;
    }

    /**
     * Send header.
     * @param  string  $name
     * @param  ?string $value
     * @return void
     * @throws froq\http\HttpException
     */
    public function sendHeader(string $name, ?string $value): void
    {
        if (headers_sent($file, $line)) {
            throw new HttpException(sprintf("Cannot use '%s()', headers already sent in %s:%s",
                __method__, $file, $line));
        }

        // null means remove
        if ($value === null) {
            $this->removeHeader($name);
            header_remove($name);
        } else {
            $this->setHeader($name, $value);
            header(sprintf('%s: %s', $name, $value));
        }
    }

    /**
     * Send headers.
     * @return void
     */
    public function sendHeaders(): void
    {
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $this->sendHeader($name, $value);
            }
        }
    }

    /**
     * Send cookie.
     * @param  string  $name
     * @param  ?string $value
     * @param  int     $expire
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     * @return void
     * @throws froq\http\HttpException
     */
    public function sendCookie(string $name, ?string $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new HttpException("Invalid cookie name '{$name}' given");
        }

        setcookie($name, (string) $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Send cookies.
     * @return void
     */
    public function sendCookies(): void
    {
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $cookie) {
                $this->sendCookie($cookie['name'], $cookie['value'], $cookie['expire'],
                    $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
            }
        }
    }

    /**
     * Set body.
     * @param  any $body
     * @return self
     * @throws froq\http\HttpException, froq\encoding\EncoderException
     */
    public function setBody($body): self
    {
        if ($body instanceof ReturnResponse) {
            $this->setStatus($body->getStatusCode())
                 ->setHeaders($body->getHeaders())
                 ->setCookies($body->getCookies());

            $body = new Body(
                $body->getContent(),
                $body->getContentType() ?? $this->body->getContentType(),
                $body->getContentCharset() ?? $this->body->getContentCharset()
            );
        }

        // no elseif, could be a Body already
        if ($body instanceof Body) {
            $body = $this->body
                ->setContent($body->getContent())
                ->setContentType($body->getContentType())
                ->setContentCharset($body->getContentCharset())
                ->getContent();
        }

        // encode/set body and body length
        if ($body !== null) {
            // json stuff (array/object returns could be encoded if content type is json)
            $bodyType = gettype($body);
            if ($bodyType != 'string') {
                switch ($bodyType) {
                    case 'array':
                    case 'object':
                        $jsonOptions = $this->app->configValue('response.json');

                        if (!empty($jsonOptions) // could be emptied by developer to disable json
                            && ($bodyContentType = $this->body->getContentType())
                            && ($bodyContentType == Body::CONTENT_TYPE_APPLICATION_JSON ||
                                $bodyContentType == Body::CONTENT_TYPE_TEXT_JSON)
                        ) {
                            [$body, $error] = Encoder::jsonEncode($body, $jsonOptions);
                            if ($error) {
                                throw new EncoderException($error);
                            }
                        }
                        break;
                    case 'integer':
                    case 'double':
                        $body = (string) $body;
                        break;
                }
            }

            // not encoded/converted
            if (!is_string($body)) {
                throw new HttpException("Body content must be string, number, array or object".
                    " (or encoded in invoked service method if froq\\http\\response\\JsonResponse".
                    " not used, or content type set as application/json or text/json),".
                    " '{$bodyType}' given");
            }

            // gzip stuff
            $gzipOptions = $this->app->configValue('response.gzip');
            $acceptEncoding = (string) $this->app->request()->getHeader('Accept-Encoding');

            $canGzip = !empty($gzipOptions) // could be emptied by developer to disable gzip
                && strpos($acceptEncoding, 'gzip') !== false
                && strlen($body) >= intval($gzipOptions['minlen'] ?? 0);

            if ($canGzip) {
                [$body, $error] = Encoder::gzipEncode($body, $gzipOptions);
                if ($error) {
                    throw new EncoderException($error);
                }

                // cancel php's compression & add required headers
                if (!headers_sent()) {
                    ini_set('zlib.output_compression', 'Off');
                }
                $this->setHeader('Content-Encoding', 'gzip');
                $this->setHeader('Vary', 'Accept-Encoding');
            }

            // finally..
            $this->body->setContent($body);
        }

        return $this;
    }

    /**
     * Get body.
     * @return any
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Send.
     * @return void
     */
    public function send(): void
    {
        // status
        if ($this->httpVersion == Http::VERSION_1_0 || $this->httpVersion == Http::VERSION_1_1) {
            header(sprintf('%s %s %s', $this->httpVersion, $this->status->getCode(), $this->status->getText()));
        } elseif ($this->httpVersion == Http::VERSION_2) {
            header(sprintf('%s %s', $this->httpVersion, $this->status->getCode()));
        }

        $contentType = $this->body->getContentType();
        $contentCharset = $this->body->getContentCharset();
        $contentLength = $this->body->getContentLength();

        // content type/charset/length
        if ($contentType == '') {
            $this->sendHeader('Content-Type', Body::CONTENT_TYPE_NONE);
        } elseif ($contentCharset == '' || strtolower($contentType) == Body::CONTENT_TYPE_NONE) {
            $this->sendHeader('Content-Type', $contentType);
        } else {
            $this->sendHeader('Content-Type', sprintf('%s; charset=%s', $contentType, $contentCharset));
        }
        $this->sendHeader('Content-Length', (string) $contentLength);

        // real load time
        $exposeAppLoadTime = $this->app->configValue('exposeAppLoadTime');
        if ($exposeAppLoadTime === true || $exposeAppLoadTime === $this->app->env()) {
            $this->sendHeader('X-App-Load-Time', $this->app->loadTime());
        }

        // print it baby!
        print $this->body->toString();
    }

    // @wait
    // public function sendFile($file): void {}

    /**
     * End.
     * @return void
     */
    public function end(): void
    {
        $this->sendHeaders();
        $this->sendCookies();
        $this->send();
    }
}
