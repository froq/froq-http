<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\Message;

/**
 * Response.
 *
 * @package froq\http\client
 * @object  froq\http\client\Response
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
final class Response extends Message
{
    /**
     * Status.
     * @var int
     */
    private int $status;

    /**
     * Parsed body.
     * @var ?array
     */
    private ?array $parsedBody = null;

    /**
     * Constructor.
     * @param int         $status
     * @param string|null $body
     * @param array|null  $parsedBody
     * @param array|null  $headers
     */
    public function __construct(int $status = 0, string $body = null, array $parsedBody = null,
        array $headers = null)
    {
        $this->setStatus($status);

        isset($parsedBody) && $this->setParsedBody($parsedBody);

        parent::__construct(Message::TYPE_RESPONSE, null, $headers, $body);
    }

    /**
     * Set status.
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
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set parsed body.
     * @param  array $parsedBody
     * @return self
     */
    public function setParsedBody(array $parsedBody): self
    {
        $this->parsedBody = $parsedBody;

        return $this;
    }

    /**
     * Get parsed body.
     * @return ?array
     */
    public function getParsedBody(): ?array
    {
        return $this->parsedBody;
    }
}

