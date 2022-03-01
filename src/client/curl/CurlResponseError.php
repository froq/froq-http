<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client\curl;

/**
 * Curl Response Error.
 *
 * An error class, only thrown when client option `throwHttpErrors` is true.
 *
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\CurlResponseError
 * @author  Kerem Güneş
 * @since   5.0
 */
class CurlResponseError extends CurlError
{
    /** @var int */
    private int $status;

    /**
     * Constructor.
     *
     * @param int      $status
     * @param mixed ...$arguments
     * @since 6.0
     */
    public function __construct(int $status, mixed ...$arguments)
    {
        $this->status = $status;

        // Update code.
        $arguments['code'] = $status;

        parent::__construct(...$arguments);
    }
}
