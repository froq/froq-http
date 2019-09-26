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
use froq\http\response\{Status, Body, Response as ResponseResponse};

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

        $this->setHeaders($this->app->config('headers', []));
        $this->setCookies($this->app->config('cookies', []));
    }

    /**
     * Set/Get status.
     * @param  ...$arguments
     * @return self|froq\http\response\Status
     */
    public function status(...$arguments)
    {
        return $arguments ? $this->setStatus(...$arguments) : $this->status;
    }

    /**
     * Set/get body.
     * @param  ...$arguments
     * @return self|froq\http\response\Body
     */
    public function body(...$arguments)
    {
        return $arguments ? $this->setBody(...$arguments) : $this->body;
    }

    /**
     * Redirect.
     * @param  string     $location
     * @param  int        $code
     * @param  array|null $headers
     * @param  array|null $cookies
     * @return self
     */
    public function redirect(string $location, int $code = Status::FOUND, array $headers = null, array $cookies = null): self
    {
        $this->setHeader('Location', trim($location))->setStatus($code);

        if ($headers != null) $this->setHeaders($headers);
        if ($cookies != null) $this->setCookies($cookies);

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
    public function sendHeader(string $name, ?string $value, bool $replace = true): void
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
            header(sprintf('%s: %s', $name, $value), $replace);
        }
    }

    /**
     * Send headers.
     * @return void
     */
    public function sendHeaders(): void
    {
        foreach ((array) $this->headers as $name => $value) {
            if (is_array($value)) { // @see Message.addHeader()
                foreach ($value as $value) {
                    $this->sendHeader($name, $value, false);
                }
            } else {
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

        $value = (string) $value;
        if ($expire < 0) {
            $value = '';
        }

        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Send cookies.
     * @return void
     */
    public function sendCookies(): void
    {
        foreach ((array) $this->cookies as $cookie) {
            $this->sendCookie($cookie['name'], $cookie['value'], $cookie['expire'],
                $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
        }
    }

    /**
     * Set body.
     * @param  any $body
     * @param  string|null $contentType
     * @param  string|null $contentCharset
     * @return self
     * @throws froq\http\HttpException, froq\encoding\EncoderException
     */
    public function setBody($body, string $contentType = null, string $contentCharset = null): self
    {
        if ($body != null) {
            if ($body instanceof Response) {
                $body = $body->getBody();
            } elseif ($body instanceof ResponseResponse) {
                $this->setStatus($body->getStatusCode())
                     ->setHeaders($body->getHeaders())
                     ->setCookies($body->getCookies());

                $body = new Body(
                    $body->getContent(),
                    $body->getContentType() ?? $this->body->getContentType(),
                    $body->getContentCharset() ?? $this->body->getContentCharset()
                );

                // override, not needed all the stuff below
                if ($body->isImage()) {
                    $this->body = $body;

                    return $this;
                }
            }

            // no elseif, could be a Body already
            if ($body instanceof Body) {
                $body = $this->body
                    ->setContent($body->getContent())
                    ->setContentType($body->getContentType())
                    ->setContentCharset($body->getContentCharset())
                    ->getContent();
            }
        }

        if ($body !== null) {
            // json stuff (array/object returns could be encoded if content type is json)
            $bodyType = gettype($body);
            if ($bodyType != 'string') {
                switch ($bodyType) {
                    case 'array': case 'object':
                        $jsonOptions = $this->app->config('response.json');
                        if ($jsonOptions != null // could be emptied by developer to disable json
                            && ($bodyContentType = $this->body->getContentType())
                            && (strpos($bodyContentType, '/json') || strpos($bodyContentType, '+json'))
                        ) {
                            [$body, $error] = Encoder::jsonEncode($body, $jsonOptions);
                            if ($error) {
                                throw new EncoderException($error);
                            }
                        }
                        break;
                    case 'integer': case 'double':
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
            $bodyLength = strlen($body);
            if ($bodyLength > 0) { // prevent gzip corruption for 0 byte data
                $gzipOptions = $this->app->config('response.gzip');
                $acceptEncoding = (string) $this->app->request()->getHeader('Accept-Encoding');

                $canGzip = $gzipOptions != null // could be emptied by developer to disable gzip
                    && strpos($acceptEncoding, 'gzip') !== false
                    && $bodyLength >= ($gzipOptions['minlen'] ?? 1);

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
            }

            // finally..
            $this->body->setContent($body)
                       ->setContentLength(strlen($body));
        }

        // '' accepted for 'none' responses
        if ($contentType !== null) {
            $this->body->setContentType($contentType);
        }
        if ($contentCharset !== null) {
            $this->body->setContentCharset($contentCharset);
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
        if ($this->httpVersion == Http::VERSION_2_0) {
            header(sprintf('%s %s', $this->httpVersion, $this->status->getCode()));
        } elseif ($this->httpVersion == Http::VERSION_1_0 || $this->httpVersion == Http::VERSION_1_1) {
            header(sprintf('%s %s %s', $this->httpVersion, $this->status->getCode(), $this->status->getText()));
        }

        // load time
        $exposeAppLoadTime = $this->app->config('exposeAppLoadTime');
        if ($exposeAppLoadTime === true || $exposeAppLoadTime === $this->app->env()) {
            $this->sendHeader('X-App-Load-Time', $this->app->loadTime());
        }

        [$contentType, $contentCharset, $contentLength] = $this->body->toArray();

        // content type/charset?/length
        if ($this->body->isImage()) {
            $this->sendHeader('Content-Type', $contentType);
        } elseif ($contentType == '') {
            $this->sendHeader('Content-Type', 'n/a');
        } elseif ($contentCharset == '' || in_array($contentType, ['n/a', 'none'])) {
            $this->sendHeader('Content-Type', $contentType);
        } else {
            $this->sendHeader('Content-Type', sprintf('%s; charset=%s', $contentType, $contentCharset));
        }
        $this->sendHeader('Content-Length', (string) $contentLength);

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
