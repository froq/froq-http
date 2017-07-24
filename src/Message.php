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
    final public function getApp(): App
    {
        return $this->app;
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
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Get header.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function getHeader(string $name, $valueDefault = null)
    {
        return $this->headers[$name] ?? $valueDefault;
    }

    /**
     * Get headers.
     * @return array
     */
    final public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set headers.
     * @param  array $headers
     * @return self
     */
    final public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
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
    final public function getCookie(string $name, $valueDefault = null)
    {
        return $this->cookies[$name] ?? $valueDefault;
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
     * Get cookies.
     * @return array
     */
    final public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Get body.
     * @return any
     */
    abstract public function getBody();
}
