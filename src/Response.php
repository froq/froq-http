<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http;

use froq\http\response\{ResponseTrait, ResponseException, Status, Cookies, Cookie};
use froq\http\message\{ContentType, ContentCharset};
use froq\http\{Http, Message};
use froq\file\{FileObject, ImageObject, Util as FileUtil};
use froq\{App, encoding\Encoder};
use Exception;

/**
 * Response.
 *
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
    protected Status $status;

    /**
     * Constructor.
     * @param froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app, Message::TYPE_RESPONSE);

        $this->status  = new Status();
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
        try {
            $this->status->setCode($code);
        } catch (Exception $e) {
            $this->status->setCode(Status::INTERNAL_SERVER_ERROR);
        }

        $this->status->setText($text ?? Status::getTextByCode($code));

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
            throw new ResponseException('Cannot use "%s()", headers already sent in "%s:%s"',
                [__method__, $file, $line]);
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
     * @throws froq\http\response\ResponseException
     */
    public function sendCookie(string $name, $value, array $options = null): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException('Cannot use "%s()", headers already sent in "%s:%s"',
                [__method__, $file, $line]);
        }

        // Check name.
        $session = $this->app->session();
        if ($session != null && $session->getName() == $name) {
            throw new ResponseException('Invalid cookie name "%s", name "%s" reserved as '.
                'session name', [$name, $name]);
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
        // Clean up above..
        while (ob_get_level()) {
            ob_end_clean();
        }

        $body = $this->getBody();
        $content = $body->getContent();
        $contentAttributes = $body->getContentAttributes();

        // Those n/a responses output nothing.
        if ($body->isNone()) {
            header('Content-Type: n/a');
            header('Content-Length: 0');
        }
        // Text contents (html, json, xml etc.).
        elseif ($body->isText()) {
            $content = (string) $content;
            $contentType = $contentAttributes['type'] ?? ContentType::TEXT_HTML; // @default
            $contentCharset = $contentAttributes['charset'] ?? ContentCharset::UTF_8; // @default
            if ($contentCharset && $contentCharset != ContentCharset::NA) {
                $contentType = sprintf('%s; charset=%s', $contentType, $contentCharset);
            }

            // Gzip stuff.
            $contentLength = strlen($content);
            if ($contentLength > 0) { // Prevent gzip corruption for 0 byte data.
                $gzipOptions = $this->app->config('response.gzip');
                $acceptEncoding = $this->app->request()->getHeader('Accept-Encoding', '');

                // Gzip options may be emptied by developer to disable gzip using null.
                if ($gzipOptions != null && $contentLength >= ($gzipOptions['minlen'] ?? 64)
                    && strpos($acceptEncoding, 'gzip') !== false) {
                    $content = Encoder::gzipEncode($content, (array) $gzipOptions, $error);
                    if ($error == null) {
                        // Cancel php's compression & add required headers.
                        ini_set('zlib.output_compression', 'off');

                        header('Vary: Accept-Encoding');
                        header('Content-Encoding: gzip');
                    }
                }
            }

            header('Content-Type: '. $contentType);
            header('Content-Length: '. strlen($content));

            echo $content;
        }
        // Image contents.
        elseif ($body->isImage()) {
            // Payload may be contain not-modified status with null content.
            if ($content == null) {
                return;
            }

            [$image, $imageType, $imageModifiedAt, $imageOptions] = [
                $content, $contentAttributes['type'], $contentAttributes['modifiedAt'],
                          $contentAttributes['options'] ?? $this->app->config('response.image')];

            $image = ImageObject::fromResource($image, $imageType, $imageOptions);
            $content = $image->toString();

            header('Content-Type: '. $imageType);
            header('Content-Length: '. strlen($content));
            if (is_int($imageModifiedAt) || is_string($imageModifiedAt)) {
                header('Last-Modified: '. Http::date(
                    is_int($imageModifiedAt) ? $imageModifiedAt : strtotime($imageModifiedAt)
                ));
            }
            header('X-Dimensions: '. vsprintf('%dx%d', $image->getDimensions()));

            echo $content;

            $image->free();
        }
        // File contents (actually file downloads).
        elseif ($body->isFile()) {
            // Payload may be contain not-modified status with null content.
            if ($content == null) {
                return;
            }

            [$file, $fileMime, $fileName, $fileSize, $fileModifiedAt] = [
                $content, $contentAttributes['mime'], $contentAttributes['name'],
                          $contentAttributes['size'], $contentAttributes['modifiedAt']];

            // If rate limit is null or -1, than file size will be used as rate limit.
            $rateLimit = (int) $this->app->config('response.file.rateLimit', -1);
            if ($rateLimit < 1) {
                $rateLimit = $fileSize;
            }

            header('Content-Type: '. ($fileMime ?: ContentType::APPLICATION_OCTET_STREAM));
            header('Content-Length: '. $fileSize);
            header('Content-Disposition: attachment; filename="'. $fileName .'"');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Expires: '. Http::date(0));
            if (is_int($fileModifiedAt) || is_string($fileModifiedAt)) {
                header('Last-Modified: '. Http::date(
                    is_int($fileModifiedAt) ? $fileModifiedAt : strtotime($fileModifiedAt)
                ));
            }
            if ($rateLimit != $fileSize) {
                header('X-Rate-Limit: '. FileUtil::formatBytes($rateLimit) .'/s');
            }

            $file = FileObject::fromResource($file);
            $file->rewind();

            do {
                $content = $file->read($rateLimit);
                echo $content;
                sleep(1); // Apply rate limit.
            } while ($content && !connection_aborted());

            $file->free();
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
            if ($this->getHttpVersionNumber() >= 2.0) {
                header(sprintf('%s %s', $this->getHttpVersion(), $code));
            } else {
                header(sprintf('%s %s %s', $this->getHttpVersion(), $code, $this->status->getText()));
            }
        }
        header('Status: '. $code);

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }
}
