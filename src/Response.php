<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
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
 * @author  Kerem Güneş
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
     * Get runtime.
     *
     * @alias to App.runtime()
     * @since 5.0
     */
    public function time(...$args)
    {
        return $this->app->runtime(...$args);
    }

    /**
     * Get status, also set its code if provided.
     *
     * @param  ... $args
     * @return froq\http\response\Status
     */
    public function status(...$args): Status
    {
        $args && $this->setStatus(...$args);

        return $this->status;
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

        $cookie = ($value instanceof Cookie) ? $value : new Cookie($name, $value, $options);

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
        if ($body->isNa()) {
            header('Content-Type: na');
            header('Content-Length: 0');

            $this->exposeAppRuntime();
        }
        // Text contents (html, json, xml etc.).
        elseif ($body->isText()) {
            $content        = (string) $content;
            $contentType    = $attributes['type']    ?? ContentType::TEXT_HTML; // @default
            $contentCharset = $attributes['charset'] ?? ContentCharset::UTF_8;  // @default
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

            $this->exposeAppRuntime();

            echo $content;
        }
        // Image contents.
        elseif ($body->isImage()) {
            // Payload may be contain not-modified status with null content.
            if ($content == null) {
                return;
            }

            [$image, $imageType, $modifiedAt, $expiresAt, $direct, $etag] = [
                $content, ...array_select($attributes, ['type', 'modifiedAt', 'expiresAt', 'direct', 'etag'])
            ];

            // For direct file readings.
            if ($direct) {
                header('Content-Type: '. $imageType);
                header('Content-Length: '. filesize($image));
                if ($etag) {
                    header('ETag: '. (is_string($etag) ? $etag : hash_file('fnv1a64', $image)));
                }
                if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                    header('Last-Modified: '. Http::date(
                        is_int($modifiedAt) ? $modifiedAt : strtotime($modifiedAt)
                    ));
                }
                if ($expiresAt && (is_int($expiresAt) || is_string($expiresAt))) {
                    header('Expires: '. Http::date(
                        is_int($expiresAt) ? $expiresAt : strtotime($expiresAt)
                    ));
                }
                header('X-Dimensions: '. vsprintf('%dx%d', getimagesize($image)));

                $this->exposeAppRuntime();

                readfile($image);
            }
            // For resize/crop purposes.
            else {
                $options = $attributes['options'] ?? $this->app->config('response.image');

                $image   = ImageObject::fromResource($image, $imageType, $options);
                $content = $image->toString();

                header('Content-Type: '. $imageType);
                header('Content-Length: '. strlen($content));
                if ($etag) {
                    header('ETag: '. (is_string($etag) ? $etag : hash('fnv1a64', $content)));
                }
                if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                    header('Last-Modified: '. Http::date(
                        is_int($modifiedAt) ? $modifiedAt : strtotime($modifiedAt)
                    ));
                }
                if ($expiresAt && (is_int($expiresAt) || is_string($expiresAt))) {
                    header('Expires: '. Http::date(
                        is_int($expiresAt) ? $expiresAt : strtotime($expiresAt)
                    ));
                }
                header('X-Dimensions: '. vsprintf('%dx%d', $image->dimensions()));

                $this->exposeAppRuntime();

                echo $content;

                unset($image); // Free.
            }
        }
        // File contents (actually file downloads).
        elseif ($body->isFile()) {
            // Payload may be contain not-modified status with null content.
            if ($content == null) {
                return;
            }

            [$file, $fileMime, $fileName, $fileSize, $modifiedAt, $direct, $rate] = [
                $content, ...array_select($attributes, ['mime', 'name', 'size', 'modifiedAt', 'direct', 'rate'])
            ];

            // If rate limit is null or -1, than file size will be used as rate limit.
            $rateLimit = $rate ?? (int) $this->app->config('response.file.rateLimit', -1);
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

            $this->exposeAppRuntime();

            // For direct file readings.
            if ($direct) {
                $file = fopen($file, 'rb');

                do {
                    echo fread($file, $rateLimit);
                    sleep(1); // Apply rate limit.
                } while (!connection_aborted() && !feof($file));

                fclose($file);
            }
            // For resource readings.
            else {
                $file = FileObject::fromResource($file);
                $file->rewind();

                do {
                    echo $file->read($rateLimit);
                    sleep(1); // Apply rate limit.
                } while (!connection_aborted() && $file->valid());

                unset($file); // Free.
            }
        }
        // Nope, nothing to print..
        // else {}

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

        header('Status: '. $code);

        if (!http_response_code($code)) {
            ($this->httpVersion >= 2.0)
                ? header(sprintf('%s %s', $this->httpProtocol, $code))
                : header(sprintf('%s %s %s', $this->httpProtocol, $code, $this->status->getText()));
        }

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }

    /**
     * @since 5.0
     * @internal
     */
    private function exposeAppRuntime(): void
    {
        $art = $this->app->config('exposeAppRuntime');
        if ($art && ($art === true || $art === $this->app->env())) {
            header('X-Art: '. $this->app->runtime(format: true));
        }
    }
}
