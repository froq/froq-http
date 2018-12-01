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

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Message
 * @author     Kerem Güneş <k-gun@mail.com>
 */
abstract class Message
{
    /**
     * App.
     * @var Froq\App
     */
    protected $app;

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
     * @param Froq\App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->httpVersion = Http::detectVersion();
    }

    /**
     * Get app.
     * @return Froq\App
     */
    public final function getApp(): App
    {
        return $this->app;
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
     * Set header.
     * @notice All these stored headers should be sent before
     * sending the last output to the client with self.send()
     * method.
     * @param  string $name
     * @param  any    $value
     * @return self
     */
    public final function setHeader(string $name, $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Get header.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public final function getHeader(string $name, $valueDefault = null)
    {
        return $this->headers[$name] ?? $valueDefault;
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
    public final function setCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new \InvalidArgumentException("Cookie name '{$name}' not accepted!");
        }

        $this->cookies[$name] = [
            'name'      => $name,     'value'  => $value,
            'expire'    => $expire,   'path'   => $path,
            'domain'    => $domain,   'secure' => $secure,
            'httpOnly'  => $httpOnly,
        ];
    }

    /**
     * Get cookie.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public final function getCookie(string $name, $valueDefault = null)
    {
        return $this->cookies[$name] ?? $valueDefault;
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
     * Get cookies.
     * @return array
     */
    public final function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Get body.
     * @return any
     */
    abstract public function getBody();
}
