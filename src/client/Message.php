<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client;

/**
 * Message.
 *
 * @package froq\http\client
 * @object  froq\http\client\Message
 * @author  Kerem Güneş
 * @since   3.0
 */
abstract class Message
{
    /** @const int */
    public final const TYPE_REQUEST  = 1,
                       TYPE_RESPONSE = 2;

    /** @var int */
    protected int $type;

    /** @var string */
    protected string $httpProtocol;

    /** @var float */
    protected float $httpVersion;

    /** @var ?array */
    protected ?array $headers = null;

    /** @var ?string */
    protected ?string $body = null;

    /**
     * Constructor.
     *
     * @param int         $type
     * @param string|null $httpProtocol
     * @param array|null  $headers
     * @param string|null $body
     */
    public function __construct(int $type, string $httpProtocol = null, array $headers = null, string $body = null)
    {
        $this->type = $type;

        $httpProtocol ??= http_protocol();
        $this->setHttpProtocol($httpProtocol);
        $this->setHttpVersion((float) substr($httpProtocol, 5, 3));

        isset($headers) && $this->setHeaders($headers);
        isset($body)    && $this->setBody($body);
    }

    /** @magic */
    public final function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Get type.
     *
     * @return int
     */
    public final function type(): int
    {
        return $this->type;
    }

    /**
     * Set HTTP protocol.
     *
     * @param  string $httpProtocol
     * @return self
     */
    public final function setHttpProtocol(string $httpProtocol): self
    {
        $this->httpProtocol = $httpProtocol;

        return $this;
    }

    /**
     * Get HTTP protocol.
     *
     * @return string
     */
    public final function getHttpProtocol(): string
    {
        return $this->httpProtocol;
    }

    /**
     * Set HTTP version.
     *
     * @param  float $httpVersion
     * @return self
     */
    public final function setHttpVersion(float $httpVersion): self
    {
        $this->httpVersion = $httpVersion;

        return $this;
    }

    /**
     * Get HTTP version.
     *
     * @return string
     */
    public final function getHttpVersion(): float
    {
        return $this->httpVersion;
    }

    /**
     * Set headers.
     *
     * @param  array $headers
     * @param  bool  $reset @internal
     * @return self
     */
    public final function setHeaders(array $headers, bool $reset = false): self
    {
        $reset && $this->headers = [];

        foreach ($headers as $key => $value) {
            $this->setHeader((string) $key, $value);
        }

        if ($this->headers) {
            ksort($this->headers);
        }

        return $this;
    }

    /**
     * Get headers.
     *
     * @return array|null
     */
    public final function getHeaders(): array|null
    {
        return $this->headers;
    }

    /**
     * Check a header existence.
     *
     * @param  string $name
     * @return bool
     */
    public final function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]) || isset($this->headers[strtolower($name)]);
    }

    /**
     * Set header.
     *
     * @param   string            $name
     * @param   string|array|null $value
     * @return  self
     */
    public final function setHeader(string $name, string|array|null $value): self
    {
        $name = strtolower($name);

        // Null means remove.
        if ($value === null) {
            unset($this->headers[$name]);
        } else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Get header.
     *
     * @param  string      $name
     * @param  string|null $default
     * @return string|array|null
     */
    public final function getHeader(string $name, string $default = null): string|array|null
    {
        return $this->headers[$name] ?? $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Set body.
     *
     * @param  string $body
     * @return self
     */
    public final function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string|null
     */
    public final function getBody(): string|null
    {
        return $this->body;
    }

    /**
     * Get string representations of message object.
     *
     * @return string
     * @since  6.0
     */
    public final function toString(): string
    {
        if ($this->type == self::TYPE_REQUEST) {
            $ret = sprintf("%s %s %s\r\n", $this->getMethod(), $this->getUri(), $this->getHttpProtocol());
        } elseif ($this->type == self::TYPE_RESPONSE) {
            $ret = sprintf("%s %s\r\n", $this->getHttpProtocol(), $this->getStatus());
        }

        $headers = $this->getHeaders();
        $body    = $this->getBody();

        if ($headers != null) {
            foreach ($headers as $name => $value) {
                // Skip first line (which is already added above).
                if ($name == '0') {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $value) {
                        $ret .= "{$name}: {$value}\r\n";
                    }
                } else {
                    $ret .= "{$name}: {$value}\r\n";
                }
            }
        }

        if ($body != null) {
            $ret .= "\r\n";
            $ret .= $body;
        }

        return $ret;
    }
}
