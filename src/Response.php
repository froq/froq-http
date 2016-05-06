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

use Froq\Util\Traits\GetterTrait as Getter;
use Froq\Http\Response\{Status, Body, BodyContent};
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
    use Getter;

    /**
     * HTTP Version.
     * @var string
     */
    private $httpVersion;

    /**
     * Status object.
     * @var Froq\Http\Response\Status
     */
    private $status;

    /**
     * Body object.
     * @var Froq\Http\Response\Body
     */
    private $body;

    /**
     * Headers object.
     * @var Froq\Http\Headers
     */
    private $headers;

    /**
     * Cookies object.
     * @var Froq\Http\Cookies
     */
    private $cookies;

    /**
     * Gzip.
     * @var Froq\Encoding\Gzip
     */
    private $gzip;

    /**
     * Constructor.
     * @param int      $status
     * @param any|null $body
     * @param array    $headers
     * @param array    $cookies
     */
    final public function __construct(int $status = Status::OK, $body = null,
        array $headers = [], array $cookies = [])
    {
        // http version
        $this->httpVersion = ($_SERVER['SERVER_PROTOCOL'] ?? Http::VERSION_1_1);

        // status
        $this->status = new Status($status);

        // body
        $this->body = new Body();

        // headers/cookies iters
        $this->headers = new Headers($headers);
        $this->cookies = new Cookies($cookies);

        // gzip
        $this->gzip = new Gzip();
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
     * Set content type.
     * @param  string $contentType
     * @return self
     */
    final public function setContentType(string $contentType): self
    {
        $this->body->content->setType($contentType);

        return $this;
    }

    /**
     * Set content charset.
     * @param  string $contentCharset
     * @return self
     */
    final public function setContentCharset(string $contentCharset): self
    {
        $this->body->content->setCharset($contentCharset);

        return $this;
    }

    /**
     * Set content length.
     * @param  int $contentLength
     * @return self
     */
    final public function setContentLength(int $contentLength): self
    {
        $this->body->content->setLength($contentLength);

        return $this;
    }

    /**
     * Set a header.
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
     * Send a header instantly.
     * @param  string $name
     * @param  any    $value
     * @return void
     */
    final public function sendHeader(string $name, $value)
    {
        if (headers_sent()) return;

        // to remove a header set value as null
        if ($value === null) {
            return $this->removeHeader($name);
        }

        header(sprintf('%s: %s', $name, $value));
     }

    /**
     * Send all stored response headers.
     * @return void
     */
    final public function sendHeaderAll()
    {
        if ($this->headers->count()) {
            foreach ($this->headers as $name => $value) {
                $this->sendHeader($name, $value);
            }
        }
    }

    /**
     * Remove a header.
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
     * Remove all headers.
     * @return void
     */
    final public function removeHeaderAll()
    {
        if ($this->headers->count()) {
            foreach ($this->headers as $name => $_) {
                $this->removeHeader($name);
            }
        }
    }

    /**
     * Set a cookie.
     * @notice All these stored cookies should be sent before
     * sending the last output to the client with self.send()
     * method.
     * @param  string $name
     * @param  any     $value
     * @param  int     $expire
     * @param  string $path
     * @param  string $domain
     * @param  bool    $secure
     * @param  bool    $httponly
     * @throws \InvalidArgumentException
     * @return void
     */
    final public function setCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = null, bool $secure = false, bool $httponly = false)
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-]+$~i', $name)) {
            throw new \InvalidArgumentException('Cookie name not accepted!');
        }

        // store cookie
        $this->cookies->set($name, [
            'name'      => $name,     'value'  => $value,
            'expire'    => $expire,   'path'   => $path,
            'domain'    => $domain,   'secure' => $secure,
            'httponly'  => $httponly,
        ]);
    }

    /**
     * Send a cookie instantly.
     * @param  string  $name
     * @param  any     $value
     * @param  int     $expire
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httponly
     * @throws \InvalidArgumentException
     * @return bool
     */
    final public function sendCookie(string $name, $value, int $expire = 0,
        string $path = '/', string $domain = null, bool $secure = false, bool $httponly = false): bool
    {
        // check name
        if (!preg_match('~^[a-z0-9_\-]+$~i', $name)) {
            throw new \InvalidArgumentException('Cookie name not accepted!');
        }

        // send cookie
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Send all stored cookies.
     * @return void
     */
    final public function sendCookieAll() {
        if ($this->cookies->count()) {
            foreach ($this->cookies as $cookie) {
                $this->sendCookie($cookie['name'], $cookie['value'], $cookie['expire'],
                    $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
            }
        }
    }

    /**
     * Remove a cookie.
     * @param  string $name
     * @param  bool   $defer
     * @return void
     */
    final public function removeCookie(string $name, bool $defer = false)
    {
        unset($this->cookies[$name]);

        // remove instantly?
        if (!$defer) {
            $this->sendCookie($name, null, 322869600);
        }
    }

    /**
     * Remove all cookies.
     * @return void
     */
    final public function removeCookieAll()
    {
        if ($this->cookies->count()) {
            foreach ($this->cookies as $name => $_) {
                $this->removeCookie($name);
            }
        }
    }

    /**
     * Set body.
     * @param  any $body
     * @return self
     */
    final public function setBody($body): self
    {
        switch ($this->body->content->getType()) {
            case BodyContent::TYPE_JSON:
                break;
        }

        $this->body->content->setData($body);
        $this->body->content->setLength(strlen($body));

        return $this;
    }

    /**
     * Send status, content type and body.
     * @return void
     */
    final public function send()
    {
        // send status
        header(sprintf('%s %s',
            $this->httpVersion, $this->status->toString()));

        // body stuff
        $bodyContentType    = $this->body->content->getType();
        $bodyContentCharset = $this->body->content->getCharset();
        $bodyContentLength  = $this->body->content->getLength();

        // content type / length
        // if (empty($bodyContentType)) {
        //     $this->sendHeader('Content-Type', Content::TYPE_NONE);
        // } elseif (empty($bodyContentCharset)
        //     || strtolower($bodyContentType) == Content::TYPE_NONE) {
        //         $this->sendHeader('Content-Type', $bodyContentType);
        // } else {
        //     $this->sendHeader('Content-Type', sprintf('%s; charset=%s',
        //         $bodyContentType, $bodyContentCharset));
        // }
        // $this->sendHeader('Content-Length', $bodyContentLength);

        // // real load time
        // $this->sendHeader('X-Load-Time', app()->loadTime());

        // print it beybe!
        print $this->body->content->toString();
    }

    // @wait
    final public function sendFile($file) {}
}
