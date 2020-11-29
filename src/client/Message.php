<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client;

/**
 * Message.
 *
 * @package froq\http\client
 * @object  froq\http\client\Message
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
abstract class Message
{
    /**
    * Types.
    * @const int
    */
    public const TYPE_REQUEST  = 1,
                 TYPE_RESPONSE = 2;

    /**
    * Type.
    * @var int
    */
    protected int $type;

    /**
     * Http version.
     * @var string
     */
    protected string $httpVersion;

    /**
    * Headers.
    * @var ?array
    */
    protected ?array $headers = null;

    /**
    * Body.
    * @var ?string
    */
    protected ?string $body = null;

    /**
     * Constructor.
     * @param int         $type
     * @param string|null $httpVersion
     * @param array|null  $headers
     * @param string|null $body
     */
    public function __construct(int $type, string $httpVersion = null, array $headers = null,
        string $body = null)
    {
        $this->type = $type;
        $this->httpVersion = $httpVersion ?? ($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');

        isset($headers) && $this->setHeaders($headers);
        isset($body)    && $this->setBody($body);
    }

    /**
     * To string.
     * @return string
     */
    public final function __toString()
    {
        if ($this->type == self::TYPE_REQUEST) {
            $ret = sprintf("%s %s %s\r\n", $this->getMethod(), $this->getUri(), $this->getHttpVersion());
        } elseif ($this->type == self::TYPE_RESPONSE) {
            $ret = sprintf("%s %s\r\n", $this->getHttpVersion(), $this->getStatus());
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
                    foreach ($value as $valu) {
                        $ret .= "{$name}: {$valu}\r\n";
                    }
                    continue;
                }

                $ret .= "{$name}: {$value}\r\n";
            }
        }

        if ($body != null) {
            $ret .= "\r\n";
            $ret .= $body;
        }

        return $ret;
    }

    /**
     * Get type.
     * @return int
     */
    public final function getType(): int
    {
        return $this->type;
    }

    /**
     * Set http version.
     * @param  string $httpVersion
     * @return self
     */
    public final function setHttpVersion(string $httpVersion): self
    {
        $this->httpVersion = $httpVersion;

        return $this;
    }

    /**
     * Get http version.
     * @return string
     */
    public final function getHttpVersion(): string
    {
        return $this->httpVersion;
    }

    /**
     * Set headers.
     * @param  array     $headers
     * @param  bool|null $reset @internal
     * @return self
     */
    public final function setHeaders(array $headers, bool $reset = null): self
    {
        if ($reset) {
            $this->headers = [];
        }

        foreach ($headers as $key => $value) {
            $this->setHeader((string) $key, $value);
        }

        return $this;
    }

    /**
     * Get headers.
     * @return ?array
     */
    public final function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * Has header.
     * @param  string $name
     * @return bool
     */
    public final function hasHeader(string $name): bool
    {
        return $this->getHeader($name) !== null;
    }

    /**
     * Set header.
     * @param   string       $name
     * @param   scalar|array $value
     * @return  self
     */
    public final function setHeader(string $name, $value): self
    {
        // Null means remove.
        if ($value === null) {
            unset($this->headers[$name]);
        } else {
            if (is_scalar($value)) {
                $value = (string) $value;
            }
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Get header.
     * @param  string      $name
     * @param  string|null $valueDefault
     * @return string|array|null
     */
    public final function getHeader(string $name, string $valueDefault = null)
    {
        return $this->headers[$name]
            ?? $this->headers[strtolower($name)]
            ?? $valueDefault;
    }

    /**
     * Set body.
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
     * @return ?string
     */
    public final function getBody(): ?string
    {
        return $this->body;
    }
}
