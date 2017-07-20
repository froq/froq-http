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

use Froq\Util\Traits\GetterTrait;
use Froq\Http\Response\{Status, Body, Response as ReturnResponse};
use Froq\Encoding\{Gzip, GzipException, Json, JsonException};

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Response
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Response
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use GetterTrait;

    /**
     * HTTP Version.
     * @var string
     */
    private $httpVersion;

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
     * Headers.
     * @var Froq\Http\Headers
     */
    private $headers;

    /**
     * Cookies.
     * @var Froq\Http\Cookies
     */
    private $cookies;

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
     * Constructer.
     */
    final public function __construct()
    {
        $this->httpVersion = Http::detectVersion();
    }

    /**
     * Caller.
     * @param  string $method
     * @param  array  $methodArguments
     * @return any
     */
    final public function __call(string $method, array $methodArguments)
    {
        if (method_exists($this->body, $method)) {
            // proxify body methods
            return call_user_func_array([$this->body, $method], $methodArguments);
        }

        throw new \BadMethodCallException("Call to undefined method '{$method}'!");
    }

    /**
     * Init.
     * @param  array $options
     * @return self
     */
    final public function init(array $options = []): self
    {
        $app = app();

        $this->status  = new Status();
        $this->headers = new Headers($app->config['app.headers']);
        $this->cookies = new Cookies($app->config['app.cookies']);
        $this->body    = new Body();
        $this->gzip    = new Gzip();

        return $this;
    }

    /**
     * Redirect.
     * @param  string $location
     * @param  int    $code
     * @return void
     */
    final public function redirect(string $location, int $code = Status::FOUND)
    {
        $this->setStatus($code)->setHeader('Location', $location);
    }

    /**
     * Set status.
     * @param  int    $code
     * @param  string $text
     * @return self
     */
    final public function setStatus(int $code, string $text = null): self
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
    final public function setStatusCode(int $code): self
    {
        $this->status->setCode($code);

        return $this;
    }

    /**
     * Set status text.
     * @param  string $text
     * @return self
     */
    final public function setStatusText(string $text): self
    {
        $this->status->setText($text);

        return $this;
    }

    /**
     * Set header.
     * @notice All these stored headers should be sent before
     * sending the last output to the client with self.send()
     * method.
     * @param  string $name
     * @param  any    $value
     * @return self
     */
    final public function setHeader(string $name, $value): self
    {
        $this->headers->set($name, $value);

        return $this;
    }

    /**
     * Set headers.
     * @param  array $headers
     * @return self
     */
    final public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers->set($name, $value);
        }

        return $this;
    }

    /**
     * Send header.
     * @param  string $name
     * @param  any    $value
     * @return void
     */
    final public function sendHeader(string $name, $value)
    {
        if (headers_sent()) return;

        // null means 'remove'
        if ($value === null) {
            return $this->removeHeader($name);
        }

        header(sprintf('%s: %s', $name, $value));
    }

    /**
     * Send headers.
     * @return void
     */
    final public function sendHeaders()
    {
        if ($this->headers->count()) {
            foreach ($this->headers as $name => $value) {
                $this->sendHeader($name, $value);
            }
        }
    }

    /**
     * Remove header.
     * @param  string $name
     * @param  bool   $defer
     * @return void
     */
    final public function removeHeader(string $name, bool $defer = false)
    {
        unset($this->headers[$name]);

        // remove instantly?
        if (!$defer) {
            header_remove($name);
        }
    }

    /**
     * Remove headers.
     * @return void
     */
    final public function removeHeaders()
    {
        if ($this->headers->count()) {
            foreach ($this->headers as $name => $_) {
                $this->removeHeader($name);
            }
        }
    }

    /**
     * Set cookie.
     * @notice All these stored cookies should be sent before
     * sending the last output to the client with self.send()
     * method.
     * @param  string  $name
     * @param  any     $value
     * @param  int     $expire
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httpOnly
     * @throws \InvalidArgumentException
     * @return void
     */
    final public function setCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false)
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new \InvalidArgumentException("Cookie name '{$name}' not accepted!");
        }

        $this->cookies->set($name, [
            'name'      => $name,     'value'  => $value,
            'expire'    => $expire,   'path'   => $path,
            'domain'    => $domain,   'secure' => $secure,
            'httpOnly'  => $httpOnly,
        ]);
    }

    /**
     * Set cookies.
     * @param  array $cookies
     * @return self
     */
    final public function setCookies(array $cookies): self
    {
        foreach ($cookies as $name => $value) {
            $this->setCookie($name, $value);
        }

        return $this;
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
     * @throws \InvalidArgumentException
     * @return bool
     */
    final public function sendCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): bool
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new \InvalidArgumentException('Cookie name not accepted!');
        }

        return setcookie($name, (string) $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Send cookies.
     * @return void
     */
    final public function sendCookies() {
        if ($this->cookies->count()) {
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
    final public function removeCookie(string $name, bool $defer = false)
    {
        unset($this->cookies[$name]);

        // remove instantly?
        if (!$defer) {
            $this->sendCookie($name, null, 0);
        }
    }

    /**
     * Remove cookies.
     * @return void
     */
    final public function removeCookies()
    {
        if ($this->cookies->count()) {
            foreach ($this->cookies as $name => $_) {
                $this->removeCookie($name);
            }
        }
    }

    /**
     * Set GZip options.
     * @param  array $gzipOptions
     * @return self
     */
    final public function setGzipOptions(array $gzipOptions): self
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
     * @throws \InvalidArgumentException
     */
    final public function setBody($body): self
    {
        if ($body instanceof ReturnResponse) {
            $this->setStatus($body->getStatusCode() ?? Status ::OK)
                ->setHeaders($body->getHeaders())
                ->setCookies($body->getCookies())
            ;
            $body = new Body($body->getData(),
                $body->getDataType() ?? $this->body->getContentType(),
                $body->getDataCharset() ?? $this->body->getContentCharset());
        }

        // no elseif, could be a Body already
        if ($body instanceof Body) {
            $body = $this->body->setContent($body->getContent())
                ->setContentType($body->getContentType())
                ->setContentCharset($body->getContentCharset())
                ->getContent()
            ;
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
                throw new \InvalidArgumentException(
                    'Content must be string (encoded in service if ResponseJson etc. not used)!');
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
    final public function send()
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
        } elseif (empty($contentCharset)
            || strtolower($contentType) == Body::CONTENT_TYPE_NONE) {
                $this->sendHeader('Content-Type', $contentType);
        } else {
            $this->sendHeader('Content-Type', sprintf('%s; charset=%s',
                $contentType, $contentCharset));
        }
        $this->sendHeader('Content-Length', $contentLength);

        // real load time
        $app = app();
        if ($exposeAppLoadTime = $app->config['app.exposeAppLoadTime']) {
            $loadTime = sprintf('%.3f', $app->loadTime()['total']);
            if ($exposeAppLoadTime === true) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 1 && $app->isDev()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 2 && $app->isStage()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            } elseif ($exposeAppLoadTime === 3 && $app->isProduction()) {
                $this->sendHeader('X-App-Load-Time', $loadTime);
            }
        }

        // print it baby!
        print $this->body->toString();
    }

    // @wait
    final public function sendFile($file) {}
}
