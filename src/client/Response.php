<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client;

/**
 * A server response class.
 *
 * @package froq\http\client
 * @object  froq\http\client\Response
 * @author  Kerem Güneş
 * @since   3.0
 */
final class Response extends Message
{
    /** @var int */
    private int $status;

    /** @var ?array */
    private ?array $parsedBody = null;

    /**
     * Constructor.
     *
     * @param int         $status
     * @param string|null $body
     * @param array|null  $parsedBody
     * @param array|null  $headers
     */
    public function __construct(int $status = 0, string $body = null, array $parsedBody = null,
        array $headers = null)
    {
        $this->setStatus($status)
             ->setParsedBody($parsedBody);

        parent::__construct(null, $headers, $body);
    }

    /**
     * Set status.
     *
     * @param  int $status
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set parsed body.
     *
     * @param  array|null $parsedBody
     * @return self
     */
    public function setParsedBody(array|null $parsedBody): self
    {
        $this->parsedBody = $parsedBody;

        return $this;
    }

    /**
     * Get parsed body.
     *
     * @return array|null
     */
    public function getParsedBody(): array|null
    {
        return $this->parsedBody;
    }

    /**
     * Get parsed body mapping to target class/object.
     *
     * @param  string|object $target
     * @param  bool          $allowNullBody
     * @param  array         $options
     * @return object|null
     */
    public function getParsedBodyAs(string|object $target, bool $allowNullBody = true, array $options = []): object|null
    {
        if ($allowNullBody && $this->parsedBody === null) {
            return null;
        }

        $mapper = new \ObjectMapper($target, $options);
        return $mapper->map((array) $this->parsedBody);
    }
}
