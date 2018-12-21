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

namespace Froq\Http;

use Froq\App;
use Froq\Encoding\{Gzip, Json, JsonException};
use Froq\Http\Response\{Status, Body, Response as ReturnResponse};

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Response
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Response extends Message
{
    /**
     * Status.
     * @var Froq\Http\Response\Status
     */
    private $status;

    /**
     * Body.
     * @var Froq\Http\Response\Body
     */
    private $body;

    /**
     * Gzip.
     * @var Froq\Encoding\Gzip
     */
    private $gzip;

    /**
     * GZip options.
     * @var array
     */
    private $gzipOptions = [];

    /**
     * Constructor.
     * @param Froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $config = $this->app->config();
        $this->setHeaders($config['app.headers'] ?? []);
        $this->setCookies($config['app.cookies'] ?? []);

        $this->status = new Status();
        $this->body = new Body();
        $this->gzip = new Gzip();
    }

    /**
     * Call magic.
     * @param  string $method
     * @param  array  $methodArguments
     * @return any
     * @throws Froq\Http\HttpException
     */
    public function __call(string $method, array $methodArguments)
    {
        if (method_exists($this->body, $method)) {
            // proxify body methods
            return call_user_func_array([$this->body, $method], $methodArguments);
        }

        throw new HttpException("Call to undefined method '{$method}'!");
    }

    /**
     * Get status.
     * @return Froq\Http\Response\Status
     */
    public function status(): Status
    {
        return $this->status;
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
     * Get gzip.
     * @return Froq\Encoding\Gzip
     */
    public function getGzip(): Gzip
    {
        return $this->gzip;
    }

    /**
     * Get gzip options.
     * @return array
     */
    public function getGzipOptions(): array
    {
        return $this->gzipOptions;
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
     * Set status code.
     * @param  int $code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->status->setCode($code);

        return $this;
    }

    /**
     * Set status text.
     * @param  string $text
     * @return self
     */
    public function setStatusText(string $text): self
    {
        $this->status->setText($text);

        return $this;
    }

    /**
     * Send header.
     * @param  string $name
     * @param  any    $value
     * @return void
     * @throws Froq\Http\HttpException
     */
    public function sendHeader(string $name, $value): void
    {
        if (headers_sent($file, $line)) {
            throw new HttpException(sprintf("Cannot send header '%s', headers was already sent int %s:%s",
                $name, $file, $line));
        }

        // null means remove
        if ($value === null) {
            header_remove($name);
            unset($this->headers[$name]);
        } else {
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
     * @param  any     $value
     * @param  int     $expire
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     * @return void
     * @throws Froq\Http\HttpException
     */
    public function sendCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new HttpException("Invalid cookie name '{$name}' given!");
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
     * Remove cookie.
     * @param  string $name
     * @param  bool   $defer
     * @return void
     */
    public function removeCookie(string $name, bool $defer = false): void
    {
        unset($this->cookies[$name]);

        // remove instantly?
        if (!$defer) {
            $this->sendCookie($name, null, 0);
        }
    }

    /**
     * Set gzip options.
     * @param  array $gzipOptions
     * @return void
     */
    public function setGzipOptions(array $gzipOptions): void
    {
        isset($gzipOptions['level']) && $this->gzip->setLevel($gzipOptions['level']);
        isset($gzipOptions['mode']) && $this->gzip->setMode($gzipOptions['mode']);
        isset($gzipOptions['minlen']) && $this->gzip->setDataMinlen($gzipOptions['minlen']);

        $this->gzipOptions = $gzipOptions;
    }

    /**
     * Set body.
     * @param  any $body
     * @return self
     * @throws Froq\Encoding\JsonException, Froq\Http\HttpException
     */
    public function setBody($body): self
    {
        if ($body instanceof ReturnResponse) {
            $this->setStatus($body->getStatusCode() ?? Status::OK)
                ->setHeaders($body->getHeaders())
                ->setCookies($body->getCookies());

            $body = new Body(
                $body->getData(),
                $body->getDataType() ?? $this->body->getContentType(),
                $body->getDataCharset() ?? $this->body->getContentCharset()
            );
        }

        // no elseif, could be a Body already
        if ($body instanceof Body) {
            $body = $this->body->setContent($body->getContent())
                ->setContentType($body->getContentType())
                ->setContentCharset($body->getContentCharset())
                ->getContent();
        }

        // last check for body
        $bodyType = gettype($body);
        if ($bodyType != 'string') {
            // array returns could be encoded if content type is json
            if ($bodyType == 'array' && ($bodyContentType = $this->getContentType())
                    && ($bodyContentType == Body::CONTENT_TYPE_TEXT_JSON ||
                        $bodyContentType == Body::CONTENT_TYPE_APPLICATION_JSON)
            ) {
                $json = new Json($body);
                $body = $json->encode();
                if ($json->hasError()) {
                    throw new JsonException($json->getErrorMessage(), $json->getErrorCode());
                }
            } else {
                throw new HttpException('Body content must be string or array (or encoded in service'.
                    ' if ResponseJson etc. not used)!');
            }
        }

        if ($body != null) {
            // gzip
            if (!empty($this->gzipOptions)) {
                $this->gzip->setData($body);
                if ($this->gzip->checkDataMinlen()) {
                    $body = $this->gzip->encode();
                    $this->setHeaders(['Vary' => 'Accept-Encoding', 'Content-Encoding' => 'gzip']);
                }
            }

            $this->body->setContent($body)->setContentLength(strlen($body));
        }

        return $this;
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

        // body stuff
        $contentType = $this->body->getContentType();
        $contentCharset = $this->body->getContentCharset();
        $contentLength = $this->body->getContentLength();

        // content type / length
        if (empty($contentType)) {
            $this->sendHeader('Content-Type', Body::CONTENT_TYPE_NONE);
        } elseif (empty($contentCharset) || strtolower($contentType) == Body::CONTENT_TYPE_NONE) {
                $this->sendHeader('Content-Type', $contentType);
        } else {
            $this->sendHeader('Content-Type', sprintf('%s; charset=%s', $contentType, $contentCharset));
        }
        $this->sendHeader('Content-Length', $contentLength);

        // real load time
        $exposeAppLoadTime = $this->app->configValue('app.exposeAppLoadTime');
        if ($exposeAppLoadTime) {
            $loadTime = $this->app->loadTime()['s'];
            if ($exposeAppLoadTime === true) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 1 && $this->app->isDev()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 2 && $this->app->isStage()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 3 && $this->app->isProduction()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            }
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
