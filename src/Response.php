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
use froq\file\Util as FileUtil;
use froq\encoding\Util as EncodingUtil;
use froq\http\{Http, Message};
use froq\http\message\Body;
use froq\http\response\{ResponseTrait, ResponseException, Status, Cookies, Cookie};
use froq\http\common\exceptions\CookieException;

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
     * Response trait.
     * @see froq\http\response\ResponseTrait
     */
    use ResponseTrait;

    /**
     * Status.
     * @var froq\http\response\Status
     */
    protected $status;

    /**
     * Cookies.
     * @var froq\http\response\Cookies
     */
    protected $cookies;

    /**
     * Constructor.
     * @param froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app, Message::TYPE_RESPONSE);

        $this->status = new Status();
        $this->cookies = new Cookies();

        [$headers, $cookies] = $app->config(['headers', 'cookies']);
        foreach ((array) $headers as $name => $value) {
            $this->headers->add($name, $value);
        }
        foreach ((array) $cookies as $name => $options) {
            $this->cookies->add($name, Cookie::createFromOptions($name, (array) $options));
        }
    }

    /**
     * Set/get status.
     * @param  ...$arguments
     * @return self|froq\http\response\Status
     */
    public function status(...$arguments)
    {
        return $arguments ? $this->setStatus(...$arguments) : $this->status;
    }

    /**
     * Set/get cookies.
     * @param  ...$arguments
     * @return self|froq\http\response\Cookies
     */
    public function cookies(...$arguments)
    {
        return $arguments ? $this->setCookies(...$arguments): $this->cookies;
    }

    /**
     * Redirect.
     * @param  string     $to
     * @param  int        $code
     * @param  array|null $headers
     * @param  array|null $cookies
     * @return self
     */
    public function redirect(string $to, int $code = Status::FOUND, array $headers = null, array $cookies = null): self
    {
        $this->setHeader('Location', trim($to))->setStatus($code);

        $headers && $this->setHeaders($headers);
        $cookies && $this->setCookies($cookies);

        return $this;
    }

    /**
     * Set status.
     * @param  int         $code
     * @param  string|null $text
     * @return self
     */
    public function setStatus(int $code, string $text = null): self
    {
        $this->status->setCode($code);
        $this->status->setText($text ?? Status::getTextByCode($code));

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
     * Send header.
     * @param  string  $name
     * @param  ?string $value
     * @param  bool    $replace
     * @return void
     * @throws froq\http\response\ResponseException
     */
    public function sendHeader(string $name, ?string $value, bool $replace = true): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException(sprintf('Cannot use "%s()", headers already sent in "%s:%s"',
                __method__, $file, $line));
        }

        if ($value === null) {
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
        foreach ($this->headers as $name => $value) {
            if (is_array($value)) {
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
     * @param  string                                $name
     * @param  string|froq\http\response\Cookie|null $value
     * @param  array|null                            $options
     * @return void
     * @throws froq\http\response\ResponseException, froq\http\common\exceptions\CookieException
     */
    public function sendCookie(string $name, $value, array $options = null): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException(sprintf('Cannot use "%s()", headers already sent in "%s:%s"',
                __method__, $file, $line
            ));
        }

        // Check name.
        $session = $this->app->session();
        if ($session != null && $session->getName() == $name) {
            throw new CookieException(sprintf('Invalid cookie name "%s", name "%s" reserved as '.
                'session name', $name, $name));
        }

        $cookie = ($value instanceof Cookie)
            ? $value : new Cookie($name, $value, $options);

        header('Set-Cookie: '. $cookie->toString(), false);
    }

    /**
     * Send cookies.
     * @return void
     */
    public function sendCookies(): void
    {
        foreach ($this->cookies as $name => $cookie) {
            $this->sendCookie($name, $cookie);
        }
    }

    /**
     * Send body.
     * @return void
     */
    public function sendBody(): void
    {
        $content = $this->body->getContent();
        $contentAttributes = $this->body->getContentAttributes();

        // Those n/a responses output nothing.
        if ($this->body->isNone()) {
            header('Content-Type: n/a');
            header('Content-Length: 0');
        }
        // Text contents (html, json, xml etc.).
        elseif ($this->body->isText()) {
            $content = ''. $content;
            $contentType = $contentAttributes['type'] ?? Body::CONTENT_TYPE_TEXT_HTML; // @default
            $contentCharset = $contentAttributes['charset'] ?? Body::CONTENT_CHARSET_UTF_8; // @default
            if ($contentCharset != '' && $contentCharset != Body::CONTENT_CHARSET_NA) {
                $contentType = sprintf('%s; charset=%s', $contentType, $contentCharset);
            }

            // Gzip stuff.
            $len = strlen($content);
            if ($len > 0) { // Prevent gzip corruption for 0 byte data.
                $gzipOptions = $this->app->config('response.gzip');
                $acceptEncoding = (string) $this->app->request()->getHeader('Accept-Encoding');

                // Gzip options could be emptied by developer to disable gzip using null.
                if ($gzipOptions != null && $len >= ($gzipOptions['minlen'] ?? 64)
                    && strpos($acceptEncoding, 'gzip') !== false) {
                    $content = EncodingUtil::gzipEncode($content, $gzipOptions, $error);
                    if ($error == null) {
                        // Cancel php's compression & add required headers.
                        ini_set('zlib.output_compression', 'off');

                        header('Vary: Accept-Encoding');
                        header('Content-Encoding: gzip');

                        // This part is for debug purposes only.
                        header('X-Content-Encoding: gzip');
                    }
                }
            }

            header('Content-Type: '. $contentType);
            header('Content-Length: '. strlen($content));

            print $content;
        }
        // Image contents (jpeg, png and gif only).
        elseif ($this->body->isImage()) {
            [$image, $imageType, $imageModifiedAt] = [$content, $contentAttributes['type'],
                $contentAttributes['modifiedAt']];
            $xDimensions = imagesx($image) .'x'. imagesy($image);

            // Clean up above..
            while (ob_get_level()) ob_end_clean();

            ob_start();
            switch ($imageType) {
                case Body::CONTENT_TYPE_IMAGE_JPEG:
                    $jpegQuality = (int) $this->app->config('response.file.jpegQuality', -1);
                    imagejpeg($image, null, $jpegQuality) && imagedestroy($image);
                    break;
                case Body::CONTENT_TYPE_IMAGE_PNG:
                    imagepng($image) && imagedestroy($image);
                    break;
                case Body::CONTENT_TYPE_IMAGE_GIF:
                    imagegif($image) && imagedestroy($image);
                    break;
            }
            $content = ob_get_clean();

            header('Content-Type: '. $imageType);
            header('Content-Length: '. strlen($content));
            if (is_int($imageModifiedAt) || is_string($imageModifiedAt)) {
                if (!is_numeric($imageModifiedAt)) {
                    $imageModifiedAt = strtotime($imageModifiedAt);
                }
                header('Last-Modified: '. Http::date($imageModifiedAt));
            }
            header('X-Dimensions: '. $xDimensions);

            print $content;
        }
        // File contents (actually file downloads).
        elseif ($this->body->isFile()) {
            [$file, $fileType, $fileName, $fileSize, $fileMime, $fileModifiedAt] = [
                $content, $contentAttributes['type'], $contentAttributes['name'],
                $contentAttributes['size'], $contentAttributes['mime'], $contentAttributes['modifiedAt']];

            // If rate limit is null or -1, than file size will be used as rate limit.
            $rateLimit = (int) $this->app->config('response.file.rateLimit', -1);
            if ($rateLimit < 1) {
                $rateLimit = $fileSize;
            }
            $rateLimitX = FileUtil::formatBytes($rateLimit);

            // Clean up above..
            while (ob_get_level()) ob_end_clean();

            header('Content-Type: '. ($fileMime ?: Body::CONTENT_TYPE_APPLICATION_OCTET_STREAM));
            header('Content-Length: '. $fileSize);
            header('Content-Disposition: attachment; filename="'. $fileName .'"');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Expires: '. Http::date(0));
            if (is_int($fileModifiedAt) || is_string($fileModifiedAt)) {
                if (!is_numeric($fileModifiedAt)) {
                    $fileModifiedAt = strtotime($fileModifiedAt);
                }
                header('Last-Modified: '. Http::date($fileModifiedAt));
            }
            header('X-Rate-Limit: '. $rateLimitX .'/s');

            while (!feof($file) && !connection_aborted()) {
                print fread($file, $rateLimit);
                sleep(1); // Apply rate limit.
            }
            fclose($file);
        } else {
            // Nope, nothing to print..
        }
    }

    /**
     * End.
     * @return void
     */
    public function end(): void
    {
        $code = $this->status->getCode();
        if (!http_response_code($code)) {
            if (Http::parseVersion($this->httpVersion) > 2.0) {
                header(sprintf('%s %s', $this->httpVersion, $code));
            } else {
                header(sprintf('%s %s %s', $this->httpVersion, $code, $this->status->getText()));
            }
        }
        header('Status: '. $code);
        header('X-Status: '. $code);

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }
}
