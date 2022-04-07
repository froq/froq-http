<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\http\message\{Body, Cookies, Headers, ContentType};
use froq\http\response\payload\Payload;
use froq\App;

/**
 * Message.
 *
 * An abstract class, mimics HTTP Message, used by `Request` and `Response` classes
 * these mainly deals with Froq! application and controllers.
 *
 * @package froq\http
 * @object  froq\http\Message
 * @author  Kerem Güneş
 * @since   1.0
 */
abstract class Message
{
    /**
     * Types.
     * @const int
     */
    public final const TYPE_REQUEST  = 1,
                       TYPE_RESPONSE = 2;

    /** @var froq\App */
    protected App $app;

    /** @var int */
    protected int $type;

    /** @var string */
    protected string $httpProtocol;

    /** @var float */
    protected float $httpVersion;

    /** @var froq\http\message\Headers */
    protected Headers $headers;

    /** @var froq\http\message\Cookies */
    protected Cookies $cookies;

    /** @var froq\http\message\Body */
    protected Body $body;

    /**
     * Constructor.
     *
     * @param froq\App $app
     * @param int      $type
     */
    public function __construct(App $app, int $type)
    {
        $this->app          = $app;
        $this->type         = $type;

        $this->httpProtocol = Http::protocol();
        $this->httpVersion  = Http::version();

        $this->headers      = new Headers();
        $this->cookies      = new Cookies();
        $this->body         = new Body();
    }

    /**
     * Get app.
     *
     * @return froq\App
     */
    public final function getApp(): App
    {
        return $this->app;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public final function getType(): int
    {
        return $this->type;
    }

    /**
     * Get HTTP protocol.
     *
     * @return string
     * @since  5.0
     */
    public final function getHttpProtocol(): string
    {
        return $this->httpProtocol;
    }

    /**
     * Get HTTP version.
     *
     * @return float
     * @since  5.0
     */
    public final function getHttpVersion(): float
    {
        return $this->httpVersion;
    }

    /**
     * Set/get headers (set for only Response).
     *
     * @param  ...$args
     * @return static|froq\http\message\Headers
     */
    public final function headers(...$args): static|Headers
    {
        return $args ? $this->setHeaders(...$args) : $this->getHeaders();
    }

    /**
     * Set/get cookies (set for only Response).
     *
     * @param  ...$args
     * @return static|froq\http\message\Cookies
     */
    public final function cookies(...$args): static|Cookies
    {
        return $args ? $this->setCookies(...$args) : $this->getCookies();
    }

    /**
     * Set/get body.
     *
     * @param  ...$args
     * @return static|froq\http\message\Body
     */
    public final function body(...$args): static|Body
    {
        return $args ? $this->setBody(...$args) : $this->getBody();
    }

    /**
     * Add headers.
     *
     * @param  array<string, mixed> $headers
     * @return static
     * @since  4.0
     */
    public final function addHeaders(array $headers): static
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set headers.
     *
     * @param  array<string, mixed> $headers
     * @return static
     */
    public final function setHeaders(array $headers): static
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Set cookies.
     *
     * @param  array<string, mixed> $cookies
     * @return static
     */
    public final function setCookies(array $cookies): static
    {
        foreach ($cookies as $name => $value) {
            $this->setCookie($name, $value);
        }

        return $this;
    }

    /**
     * Get headers.
     *
     * @return froq\http\message\Headers
     */
    public final function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * Get cookies.
     *
     * @return froq\http\message\Cookies
     * @since  4.0
     */
    public final function getCookies(): Cookies
    {
        return $this->cookies;
    }

    /**
     * Set body.
     *
     * @param  mixed      $content
     * @param  array|null $attributes
     * @return self
     * @throws froq\http\MessageException
     */
    public final function setBody(mixed $content, array $attributes = null): self
    {
        if ($this->isRequest()) {
            $this->body->setContent($content)
                       ->setAttributes($attributes);
        } elseif ($this->isResponse()) {
            // Payload contents.
            if ($content instanceof Payload) {
                $payload = $content;
            } else {
                $attributes = (array) $attributes;
                // Content type could be set by headers before.
                $contentType = $this->header('Content-Type') ?: ContentType::TEXT_HTML;

                // Response contents (eg: return this.response(...)).
                if ($content instanceof Response) {
                    $code = $content->getStatusCode();
                    $contentType = $content->getContentType() ?? $contentType;

                    // Update attributes with current body attributes.
                    $attributes = [...$attributes, ...$content->body->getAttributes()];

                    // Update content with current body content.
                    $content = $content->body->getContent();
                } else {
                    $code = $this->getStatusCode();
                    $contentType = $this->getContentType() ?? $contentType;
                }

                // Attributes with default type if none.
                $attributes = $attributes + ['type' => $contentType];

                // Content type check for a proper response.
                $contentType = trim((string) $attributes['type'])
                    ?: throw new MessageException('Missing content type');

                if (is_array($content)) {
                    // Note: must be checked here only!
                    if (!preg_test('~json|xml~i', $contentType)) {
                        throw new MessageException(
                            'Invalid content type `%s` for `array` type content, '.
                            'content type must be denoted like `xxx/json` or `xxx/xml`',
                            $contentType
                        );
                    }
                } else {
                    // Expected, processable types.
                    if (!is_null($content) && !is_string($content)
                        && !is_image($content) && !is_stream($content)) {
                        throw new MessageException(
                            'Invalid content value type `%s`, it must be string|image|stream|null',
                            get_type($content)
                        );
                    }
                }

                $payload = new Payload($code, $content, $attributes);
            }

            // Extract needed stuff from payload process.
            [$content, $attributes, [$status, $headers, $cookies]] = $payload->process($this);

            // Set body content & attributes.
            $this->body->setContent($content)
                       ->setAttributes($attributes);

            // Set response stuff.
            $status  && $this->setStatus($status);
            $headers && $this->setHeaders($headers);
            $cookies && $this->setCookies($cookies);
        }

        return $this;
    }

    /**
     * Get body.
     *
     * @return froq\http\message\Body
     */
    public final function getBody(): Body
    {
        return $this->body;
    }

    /**
     * Get whether message is request.
     *
     * @return bool
     */
    public final function isRequest(): bool
    {
        return ($this->type == self::TYPE_REQUEST);
    }

    /**
     * Get whether message is response.
     *
     * @return bool
     */
    public final function isResponse(): bool
    {
        return ($this->type == self::TYPE_RESPONSE);
    }
}
