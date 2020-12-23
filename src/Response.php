<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http;

use froq\http\response\{ResponseTrait, ResponseException, Status, StatusException, Cookie, Cookies};
use froq\http\message\{ContentType, ContentCharset};
use froq\http\{Http, Message};
use froq\file\{object\FileObject, object\ImageObject, Util as FileUtil};
use froq\{App, encoding\Encoder};

/**
 * Response.
 *
 * Represents a HTTP response entity which extends `Message` class and mainly deals with Froq! application and
 * controllers.
 *
 * @package froq\http
 * @object  froq\http\Response
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Response extends Message
{
    /** @see froq\http\response\ResponseTrait */
    use ResponseTrait;

    /** @var froq\http\response\Status */
    protected Status $status;

    /**
     * Constructor.
     *
     * @param froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app, Message::TYPE_RESPONSE);

        $this->status = new Status();
    }

    /**
     * Set/get status.
     *
     * @param  ... $args
     * @return self|froq\http\response\Status
     */
    public function status(...$args)
    {
        return $args ? $this->setStatus(...$args) : $this->status;
    }

    /**
     * Redirect client to given location with/without given headers and cookies.
     *
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
     * Set status code and optionally status text.
     *
     * @param  int         $code
     * @param  string|null $text
     * @return self
     */
    public function setStatus(int $code, string $text = null): self
    {
        try {
            $this->status->setCode($code);
        } catch (StatusException) {
            $this->status->setCode(Status::INTERNAL_SERVER_ERROR);
        }

        // Not needed for HTTP/2 version.
        if ($this->httpVersion < 2.0) {
            $this->status->setText($text ?? Status::getTextByCode($code));
        }

        return $this;
    }

    /**
     * Send a header.
     *
     * @param  string      $name
     * @param  string|null $value
     * @param  bool        $replace
     * @return void
     * @throws froq\http\response\ResponseException
     */
    public function sendHeader(string $name, string|null $value, bool $replace = true): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException('Cannot use %s(), headers already sent at %s:%s',
                [__method__, $file, $line]);
        }

        if (is_null($value)) {
            header_remove($name);
        } else {
            header(sprintf('%s: %s', $name, $value), $replace);
        }
    }

    /**
     * Send all holding headers.
     *
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
     * Send a cookie.
     *
     * @param  string                                $name
     * @param  string|froq\http\response\Cookie|null $value
     * @param  array|null                            $options
     * @return void
     * @throws froq\http\response\ResponseException
     */
    public function sendCookie(string $name, string|Cookie|null $value, array $options = null): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException('Cannot use %s(), headers already sent at %s:%s',
                [__method__, $file, $line]);
        }

        // Check name.
        $session = $this->app->session();
        if ($session != null && $session->name() == $name) {
            throw new ResponseException('Invalid cookie name `%s`, name is reserved as session name', $name);
        }

        $cookie = ($value instanceof Cookie)
            ? $value : new Cookie($name, $value, $options);

        header('Set-Cookie: ' . $cookie->toString(), false);
    }

    /**
     * Send all holding cookies.
     *
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
     *
     * @return void
     */
    public function sendBody(): void
    {
        // Clean up above..
        while (ob_get_level()) {
            ob_end_clean();
        }

        $body       = $this->body;
        $content    = $body->getContent();
        $attributes = $body->getAttributes();

        // Those n/a responses output nothing.
        if ($body->isNone()) {
            header('Content-Type: n/a');
            header('Content-Length: 0');
        }
        // Text contents (html, json, xml etc.).
        elseif ($body->isText()) {
            $content        = (string) $content;
            $contentType    = $attributes['type']    ?? ContentType::TEXT_HTML; // @default
            $contentCharset = $attributes['charset'] ?? ContentCharset::UTF_8; // @default
            if ($contentCharset && $contentCharset != ContentCharset::NA) {
                $contentType = sprintf('%s; charset=%s', $contentType, $contentCharset);
            }

            // Gzip stuff.
            $contentLength = strlen($content);
            if ($contentLength > 0) { // Prevent gzip corruption for 0 byte data.
                $gzipOptions       = $this->app->config('response.gzip');
                $gzipOptionsMinlen = $gzipOptions ? ($gzipOptions['minlen'] ?? 64) : null;

                // Gzip options may be emptied by developer to disable gzip using null.
                if ($gzipOptions && $contentLength >= $gzipOptionsMinlen && str_contains(
                    $this->app->request()->getHeader('Accept-Encoding', ''), 'gzip'
                )) {
                    $temp = Encoder::gzipEncode($content, (array) $gzipOptions, $error);
                    if ($temp && $error == null) {
                        [$content, $temp] = [$temp, null];

                        // Cancel PHP compression.
                        ini_set('zlib.output_compression', 'off');

                        // Add required headers.
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

            [$image, $imageType, $modifiedAt, $options] = [
                $content, $attributes['type'], $attributes['modifiedAt'],
                          $attributes['options'] ?? $this->app->config('response.image')
            ];

            $image   = ImageObject::fromResource($image, $imageType, $options);
            $content = $image->toString();

            header('Content-Type: '. $imageType);
            header('Content-Length: '. strlen($content));
            if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                header('Last-Modified: '. Http::date(
                    is_int($modifiedAt) ? $modifiedAt : strtotime($modifiedAt)
                ));
            }
            header('X-Dimensions: '. vsprintf('%dx%d', $image->dimensions()));

            echo $content;

            unset($image); // Free.
        }
        // File contents (actually file downloads).
        elseif ($body->isFile()) {
            // Payload may be contain not-modified status with null content.
            if ($content == null) {
                return;
            }

            [$file, $fileMime, $fileName, $fileSize, $modifiedAt] = [
                $content, $attributes['mime'], $attributes['name'],
                          $attributes['size'], $attributes['modifiedAt']
            ];

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
            header('Expires: 0');
            if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                header('Last-Modified: '. Http::date(
                    is_int($modifiedAt) ? $modifiedAt : strtotime($modifiedAt)
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

            unset($file); // Free.
        } else {
            // Nope, nothing to print..
        }

        // Free.
        $body->setContent(null);
    }

    /**
     * End.
     *
     * @return void
     */
    public function end(): void
    {
        $code = $this->status->getCode();

        if (!http_response_code($code)) {
            ($this->httpVersion >= 2.0)
                ? header(sprintf('%s %s', $this->httpProtocol, $code))
                : header(sprintf('%s %s %s', $this->httpProtocol, $code, $this->status->getText()));
        }

        header('Status: '. $code);

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }
}
