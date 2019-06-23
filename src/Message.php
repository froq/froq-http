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
     * @var array
     */
    protected $headers = [];

    /**
     * Cookies.
     * @var array
     */
    protected $cookies = [];

    /**
     * Constructor.
     * @param froq\App $app
     * @param int      $type
     */
    public function __construct(App $app, int $type)
    {
        $this->app = $app;
        $this->type = $type;
        $this->httpVersion = Http::detectVersion();
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
    public function getType(): int
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
     * Has header.
     * @param  string $name
     * @return bool
     */
    public final function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Add header.
     * @param string  $name
     * @param ?string $value
     * @param bool    $replace
     */
    public final function addHeader(string $name, ?string $value, bool $replace = true): self
    {
        if ($this->type == self::TYPE_REQUEST) {
            throw new HttpException('You cannot modify request headers');
        }

        if ($replace) {
            $this->headers[$name] = $value;
        } else {
            $this->headers[$name] = (array) ($this->headers[$name] ?? []);
            $this->headers[$name][] = $value;
        }

        return $this;
    }

    /**
     * Set header.
     * @notice All these stored headers should be sent before sending the last output to the client
     * with self.send() method.
     * @param  string  $name
     * @param  ?string $value
     * @return self
     */
    public final function setHeader(string $name, ?string $value): self
    {
        if ($this->type == self::TYPE_REQUEST) {
            throw new HttpException('You cannot modify request headers');
        }

        $this->headers[$name] = $value;

        return $this;
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
     * Get header.
     * @param  string  $name
     * @param  ?string $valueDefault
     * @return ?string
     */
    public final function getHeader(string $name, ?string $valueDefault = null): ?string
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        $_name = strtolower($name);
        foreach ($this->headers as $name => $value) {
            if ($_name == strtolower($name)) {
                return $value;
            }
        }

        return $valueDefault;
    }

    /**
     * Get headers.
     * @return array
     */
    public final function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Remove header.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     */
    public final function removeHeader(string $name, bool $defer = true): self
    {
        if ($this->type == self::TYPE_REQUEST) {
            throw new HttpException('You cannot modify request headers');
        }

        unset($this->headers[$name]);
        if (!$defer) { // remove instantly
            header_remove($name);
        }

        return $this;
    }

    /**
     * Has cookie.
     * @param  string $name
     * @return bool
     */
    public final function hasCookie(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Set cookie.
     * @notice All these stored cookies should be sent before sending the last output to the client
     * with self::send() method.
     * @param  string            $name
     * @param  string|array|null $value
     * @param  int               $expire
     * @param  string            $path
     * @param  string            $domain
     * @param  bool              $secure
     * @param  bool              $httpOnly
     * @throws froq\http\HttpException
     * @return self
     */
    public final function setCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): self
    {
        if ($this->type == self::TYPE_REQUEST) {
            throw new HttpException('You cannot modify request cookies');
        }

        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new HttpException("Invalid cookie name '{$name}' given");
        }

        if (is_array($value)) {
            @ [$value, $expire, $path, $domain, $secure, $httpOnly] = $value;
        }

        $this->cookies[$name] = [
            'name'   => $name,                'value'    => strval($value),  'expire' => intval($expire),
            'path'   => strval($path ?? '/'), 'domain'   => strval($domain),
            'secure' => !!$secure,            'httpOnly' => !!$httpOnly
        ];

        return $this;
    }

    /**
     * Set cookies.
     * @param  array $cookies
     * @return self
     */
    public final function setCookies(array $cookies): self
    {
        foreach ($cookies as $name => $value) {
            $this->setCookie($name, $value);
        }

        return $this;
    }

    /**
     * Get cookie.
     * @param  string  $name
     * @param  ?string $valueDefault
     * @return ?string
     */
    public final function getCookie(string $name, ?string $valueDefault = null): ?string
    {
        return $this->cookies[$name] ?? $valueDefault;
    }

    /**
     * Get cookies.
     * @return array
     */
    public final function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Remove cookie.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     * @throws froq\http\HttpException
     */
    public function removeCookie(string $name, bool $defer = false): self
    {
        if ($this->type == self::TYPE_REQUEST) {
            throw new HttpException('You cannot modify request cookies');
        }

        unset($this->cookies[$name]);
        if (!$defer) { // remove instantly
            $this->sendCookie($name, null, 0);
        }

        return $this;
    }

    /**
     * Header.
     * @alias of self.getHeader(),self.setHeader(),self.addHeader()
     */
    public final function header(string $name, string $value = null, bool $replace = true)
    {
        if ($value == null) {
            return $this->getHeader($name);
        }

        return $replace ? $this->setHeader($name, $value)
                        : $this->addHeader($name, $value, false);
    }

    /**
     * Headers.
     * @alias of self.getHeaders(),self.setHeaders()
     */
    public final function headers(array $headers = null): array
    {
        return ($headers == null) ? $this->getHeaders() : $this->setHeaders($headers);
    }

    /**
     * Cookie.
     * @alias of self.getCookie(),self.setCookie()
     */
    public final function cookie(string $name, string $value = null)
    {
        return ($value === null) ? $this->getCookie($name) : $this->setCookie($name, $value);
    }

    /**
     * Cookies.
     * @alias of self.getCookies(),self.setCookies()
     */
    public final function cookies(array $cookies = null): array
    {
        return ($cookies == null) ? $this->getCookies() : $this->getCookies($cookies);
    }

    /**
     * Get body.
     * @return any
     */
    public abstract function getBody();
}
