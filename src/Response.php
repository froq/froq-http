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
use Froq\Encoding\{Encoder, EncoderException};
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
     * Constructor.
     * @param Froq\App $app
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
     * @throws Froq\Http\HttpException
     */
    public function sendHeader(string $name, ?string $value): void
    {
        if (headers_sent($file, $line)) {
            throw new HttpException(sprintf("Cannot send header '%s', headers was already sent int %s:%s",
                $name, $file, $line));
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
     * @throws Froq\Http\HttpException
     */
    public function sendCookie(string $name, ?string $value, int $expire = 0,
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
     * Set body.
     * @param  any $body
     * @return self
     * @throws Froq\Http\HttpException, Froq\Encoding\EncoderException
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
                    && ($bodyContentType == Body::CONTENT_TYPE_APPLICATION_JSON ||
                        $bodyContentType == Body::CONTENT_TYPE_TEXT_JSON)
            ) {
                $encoder = Encoder::init('json');
                $body = $encoder->encode($body);
                if ($encoder->hasError()) {
                    throw new EncoderException(sprintf('JSON Error: %s!', $encoder->getError()));
                }
            } else {
                throw new HttpException('Body content must be string or array (or encoded in service'.
                    ' if ResponseJson etc. not used)!');
            }
        }

        if ($body != null) {
            // gzip
            $gzipOptions = $this->app->configValue('gzip', []);
            $acceptEncoding = $this->app->request()->getHeader('Accept-Encoding', '');

            // check config & client
            $useGzip = $gzipOptions['use'] ?? false;
            $usesGzip = strpos($acceptEncoding, 'gzip') !== false;

            $bodyLength = strlen($body);
            $bodyLengthMin = $gzipOptions['minlen'] ?? 0;

            if ($useGzip && $usesGzip && $bodyLength >= $bodyLengthMin) {
                $encoder = Encoder::init('gzip', $gzipOptions);
                $body = $encoder->encode($body);
                if ($encoder->hasError()) {
                    throw new EncoderException(sprintf('GZip Error: %s!', $encoder->getError()));
                }

                // cancel php's compression & add required headers
                if (!headers_sent()) {
                    ini_set('zlib.output_compression', 'Off');
                }
                $this->setHeader('Content-Encoding', 'gzip');
                $this->setHeader('Vary', 'Accept-Encoding');
            }

            $this->body->setContent($body)->setContentLength($bodyLength);
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
        $this->sendHeader('Content-Length', (string) $contentLength);

        // real load time
        $exposeAppLoadTime = $this->app->configValue('exposeAppLoadTime');
        if ($exposeAppLoadTime === true || $exposeAppLoadTime === $this->app->env()) {
            $this->sendHeader('X-App-Load-Time', $this->app->loadTime()['s']);
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
