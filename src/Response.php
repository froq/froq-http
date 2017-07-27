<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
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
     * Caller.
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
     * @return void
     */
    public function redirect(string $location, int $code = Status::FOUND): void
    {
        $this->setStatus($code)->setHeader('Location', trim($location));
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
        if (headers_sent($file, $line))
            throw new HttpException(sprintf("Cannot send header '%s', headers was already sent int %s:%s",
                $name, $file, $line));

        // null means 'remove'
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
     * @return bool
     * @throws Froq\Http\HttpException
     */
    public function sendCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): bool
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new HttpException("Invalid cookie name '{$name}' given!");
        }

        return setcookie($name, (string) $value, $expire, $path, $domain, $secure, $httpOnly);
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
     * @return self
     */
    public function setGzipOptions(array $gzipOptions): self
    {
        isset($gzipOptions['level']) &&
            $this->gzip->setLevel($gzipOptions['level']);
        isset($gzipOptions['mode']) &&
            $this->gzip->setMode($gzipOptions['mode']);
        isset($gzipOptions['minlen']) &&
            $this->gzip->setDataMinlen($gzipOptions['minlen']);

        $this->gzipOptions = $gzipOptions;

        return $this;
    }

    /**
     * Set body.
     * @param  any $body
     * @return self
     * @throws Froq\Http\HttpException
     */
    public function setBody($body): self
    {
        if ($body instanceof ReturnResponse) {
            $this->setStatus($body->getStatusCode() ?? Status ::OK)
                ->setHeaders($body->getHeaders())
                ->setCookies($body->getCookies());

            $body = new Body($body->getData(),
                $body->getDataType() ?? $this->body->getContentType(),
                $body->getDataCharset() ?? $this->body->getContentCharset());
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
                throw new HttpException('Content must be string (encoded in service if ResponseJson etc. not used)!');
            }
        }

        if ($body) {
            // gzip
            if (!empty($this->gzipOptions)) {
                $this->gzip->setData($body);
                if ($this->gzip->checkDataMinlen()) {
                    $body = $this->gzip->encode();
                    $this->setHeaders(['Vary' => 'Accept-Encoding', 'Content-Encoding' => 'gzip']);
                }
            }

            $this->body->setContent($body)
                ->setContentLength(strlen($body));
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
        header(sprintf('%s %s', $this->httpVersion, $this->status->toString()));

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
        if ($exposeAppLoadTime = $this->app->configValue('app.exposeAppLoadTime')) {
            $loadTime = sprintf('%.3f', $this->app->loadTime()['total']);
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
    public function sendFile($file): void {}
}
