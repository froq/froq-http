<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http;

use froq\http\common\ResponseTrait;
use froq\http\response\{Status, StatusException};
use froq\http\message\{ContentType, ContentCharset};
use froq\encoding\encoder\{GZipEncoder, ZLibEncoder};
use froq\file\object\{ImageObject, FileObject};
use froq\{App, util\Util};
use Assert;

/**
 * An HTTP response class, extends `Message` class and mainly deals with Froq! application
 * and controllers.
 *
 * @package froq\http
 * @class   froq\http\Response
 * @author  Kerem Güneş
 * @since   1.0
 */
class Response extends Message
{
    use ResponseTrait;

    /** Status instance. */
    public readonly Status $status;

    /**
     * Constructor.
     *
     * @param froq\App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->status = new Status();
        $this->setStatus(Status::OK);
    }

    /**
     * Get runtime.
     *
     * @alias App.runtime()
     * @since 5.0
     */
    public function time(...$args)
    {
        return $this->app->runtime(...$args);
    }

    /**
     * Set/get status.
     *
     * @param  ...$args
     * @return self|froq\http\response\Status
     */
    public function status(...$args): self|Status
    {
        return $args ? $this->setStatus(...$args) : $this->getStatus();
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
        // For invalid codes.
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
     * Get status.
     *
     * @return froq\http\response\Status
     */
    public function getStatus(): Status
    {
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
     * Send a header.
     *
     * @param  string            $name
     * @param  string|array|null $value
     * @param  bool              $replace
     * @return void
     * @throws froq\http\ResponseException
     */
    public function sendHeader(string $name, string|array|null $value, bool $replace = true): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException('Cannot use %s(), headers already sent at %s:%s',
                [__METHOD__, $file, $line]);
        }

        // Multi-headers.
        if (is_array($value)) {
            foreach ($value as $value) {
                $this->sendHeader($name, $value, false);
            }
            return;
        }

        $header = http_build_header($name, $value);
        $header || throw new ResponseException('Invalid header name, it\'s empty');

        $this->head($name, $value, $replace);
    }

    /**
     * Send all headers.
     *
     * @return void
     */
    public function sendHeaders(): void
    {
        foreach ($this->headers->toArray() as $name => $value) {
            $this->sendHeader($name, $value);
        }
    }

    /**
     * Send a cookie.
     *
     * @param  string            $name
     * @param  string|array|null $value
     * @param  array|null        $options
     * @return void
     * @throws froq\http\ResponseException
     */
    public function sendCookie(string $name, string|array|null $value, array $options = null): void
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException('Cannot use %s(), headers already sent at %s:%s',
                [__METHOD__, $file, $line]);
        }

        // Protect session name.
        if ($name === $this->app->session?->option('name')) {
            throw new ResponseException('Invalid cookie name %q, it\'s reserved as session name',
                $name);
        }

        // Generally by CookieTrait.setCookie().
        if (is_array($value)) {
            $value   = $value['value'];
            $options = $value['options'] ?? null;
        }

        $cookie = http_build_cookie($name, $value, $options);
        $cookie || throw new ResponseException('Invalid cookie name, it\'s empty');

        $this->head('Set-Cookie', $cookie, false);
    }

    /**
     * Send all cookies.
     *
     * @return void
     */
    public function sendCookies(): void
    {
        foreach ($this->cookies->toArray() as $name => $cookie) {
            $this->sendCookie($name, $cookie);
        }
    }

    /**
     * Send body.
     *
     * @return void
     * @throws froq\http\ResponseException
     */
    public function sendBody(): void
    {
        // Clean up above.
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Check non-body stuff.
        if (!$this->allowsBody()) {
            return;
        }

        $content    = $this->body->getContent();
        $attributes = $this->body->getAttributes();

        // Those n/a responses output nothing.
        if ($this->body->isNa()) {
            $this->echo(['Content-Type' => 'n/a', 'Content-Length' => 0]);
        }
        // Text contents (html, json, xml etc.).
        elseif ($this->body->isText()) {
            $content        = (string) $content;
            $contentType    = $attributes['type']    ?? ContentType::TEXT_HTML; // @default
            $contentCharset = $attributes['charset'] ?? ContentCharset::UTF_8;  // @default
            $contentLength  = strlen($content);

            if ($contentCharset && $contentCharset !== ContentCharset::NA) {
                $contentType = sprintf('%s; charset=%s', $contentType, $contentCharset);
            }

            $headers = ['Content-Type' => $contentType, 'Content-Length' => $contentLength];

            // Prevent gzip corruption for 0 byte data.
            if ($contentLength > 0) {
                // This can be disabled leaving empty.
                $compressOptions = (array) $this->app->config('response.compress');

                if ($compressOptions) {
                    $compressType   = $compressOptions[0] ?? null;      // Compress types: gzip, zlib.
                    $compressLevel  = $compressOptions['level'] ?? -1;  // Compress level: -1 as default.
                    $compressMinlen = $compressOptions['minlen'] ?? 64; // Compress minlen: 64 bytes as default.

                    Assert::equals($compressType, ['gzip', 'zlib'], new ResponseException(
                        "Config option 'response.compress[0]' must be 'gzip' or 'zlib', %q given",
                        $compressType
                    ));

                    $acceptEncoding  = (string) $this->app->request->getHeader('Accept-Encoding');
                    $contentEncoding = ($compressType === 'gzip') ? 'gzip' : 'deflate';

                    if ($contentLength >= $compressMinlen && str_contains($acceptEncoding, $contentEncoding)) {
                        $encoder = ($contentEncoding === 'gzip')
                            ? new GZipEncoder(['level' => $compressLevel])
                            : new ZLibEncoder(['level' => $compressLevel]);

                        $encoder->setInput($content);
                        if ($encoder->encode()) {
                            $content = $encoder->getOutput();
                            unset($encoder);

                            // Cancel PHP compression.
                            ini_set('zlib.output_compression', false);

                            // Add related headers.
                            $headers['Vary'] = 'Accept-Encoding';
                            $headers['Content-Encoding'] = $contentEncoding;

                            // Update content length.
                            $headers['Content-Length'] = strlen($content);
                        }
                    }
                }
            }

            $this->echo($headers, $content);
        }
        // Image contents.
        elseif ($this->body->isImage()) {
            [$image, $imageType, $modifiedAt, $expiresAt, $direct, $etag] = [
                $content, ...array_select($attributes, ['type', 'modifiedAt', 'expiresAt', 'direct', 'etag'])
            ];

            $headers = ['Content-Type' => $imageType];

            // For direct file read.
            if ($direct) {
                $headers['Content-Length'] = filesize($image);

                if ($etag) {
                    $headers['ETag'] = is_string($etag) ? $etag : hash_file('fnv1a64', $image);
                }
                if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                    $headers['Last-Modified'] = Http::date($modifiedAt);
                }
                if ($expiresAt && (is_int($expiresAt) || is_string($expiresAt))) {
                    $headers['Expires'] = Http::date($expiresAt);
                }

                $headers['X-Dimensions'] = vsprintf('%dx%d', getimagesize($image));

                $this->echo($headers);

                readfile($image);
            }
            // For resize/crop purposes.
            else {
                $options = $attributes['options'] ?? $this->app->config('response.image');
                $options = $options ? (array) $options : null;

                $image   = new ImageObject($image, $imageType, $options);
                $content = $image->toString();

                $headers['Content-Length'] = strlen($content);

                if ($etag) {
                    $headers['ETag'] = is_string($etag) ? $etag : hash('fnv1a64', $content);
                }
                if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                    $headers['Last-Modified'] = Http::date($modifiedAt);
                }
                if ($expiresAt && (is_int($expiresAt) || is_string($expiresAt))) {
                    $headers['Expires'] = Http::date($expiresAt);
                }

                $headers['X-Dimensions'] = vsprintf('%dx%d', $image->dimensions());

                $this->echo($headers, $content);

                unset($image); // Free.
            }
        }
        // File contents (actually file downloads).
        elseif ($this->body->isFile()) {
            [$file, $fileMime, $fileName, $fileSize, $modifiedAt, $direct, $rateLimit] = [
                $content, ...array_select($attributes, ['mime', 'name', 'size', 'modifiedAt', 'direct', 'rateLimit'])
            ];

            // If rate limit is null or -1, than file size will be used as rate limit.
            $rateLimit ??= (int) $this->app->config('response.file.rateLimit', -1);
            if ($rateLimit < 1) {
                $rateLimit = $fileSize;
            }

            $headers = [
                'Content-Type' => $fileMime ?: ContentType::APPLICATION_OCTET_STREAM,
                'Content-Length' => $fileSize,
            ];

            $headers['Content-Disposition'] = sprintf('attachment; filename="%s"', $fileName);
            $headers['Content-Transfer-Encoding'] = 'binary';
            $headers['Cache-Control'] = 'no-cache';
            $headers['Pragma'] = 'no-cache';
            $headers['Expires'] = '0';

            if ($modifiedAt && (is_int($modifiedAt) || is_string($modifiedAt))) {
                $headers['Last-Modified'] = Http::date($modifiedAt);
            }
            if ($rateLimit !== $fileSize) {
                $headers['X-Rate-Limit'] = Util::formatBytes($rateLimit) . '/s';
            }

            $this->echo($headers);

            // For direct file read.
            if ($direct) {
                readfile($file);
            }
            // For resource read.
            else {
                $file = new FileObject($file);
                $file->rewind();

                do {
                    echo $file->read($rateLimit);
                    sleep(1); // Apply rate limit.
                } while ($file->valid() && !connection_aborted());

                unset($file); // Free.
            }
        }
        // Nothing to print.
        // else {}
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

        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }

    /**
     * Head(er) wrapper.
     */
    private function head(string $name, string|int|null $value, bool $replace = true): void
    {
        // Remove directive.
        if ($value === null) {
            header_remove($name);
        } else {
            header($name .': '. $value, $replace);
        }
    }

    /**
     * Echo wrapper.
     */
    private function echo(array $headers, string|null $output = null): void
    {
        $this->free();
        $this->expose();

        // Print headers.
        foreach ($headers as $name => $value) {
            $this->head($name, $value);
        }

        // Print output.
        if ($output !== null) {
            echo $output;
        }
    }

    /**
     * Free up body content.
     */
    private function free(): void
    {
        $this->body->setContent(null);
    }

    /**
     * Expose app runtime if available.
     */
    private function expose(): void
    {
        $art = $this->app->config('exposeAppRuntime');
        if ($art && ($art === true || $art === $this->app->env)) {
            $this->head('X-Art', $this->app->runtime(format: true));
        }
    }
}
