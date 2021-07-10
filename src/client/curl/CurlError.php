<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client\curl;

use froq\common\Error;

/**
 * Curl Error.
 *
 * Represents an error object that contains a method stack that can be used to detect mostly occured
 * cURL errors. For more error check can be done with `CURLE_*` constants using `CurlError.getCode()`
 * method.
 *
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\CurlError
 * @author  Kerem Güneş
 * @since   4.0
 */
class CurlError extends Error
{
    /**
     * Checks error is CURLE_URL_MALFORMAT(3).
     *
     * @return bool
     */
    public function isBadUrl(): bool
    {
        return ($this->code == 3);
    }

    /**
     * Checks error is CURLE_COULDNT_RESOLVE_HOST(6).
     *
     * @return bool
     */
    public function isBadHost(): bool
    {
        return ($this->code == 6);
    }

    /**
     * Checks error is CURLE_OPERATION_TIMEDOUT(28), same CURLE_OPERATION_TIMEOUTED(28).
     *
     * @return bool
     */
    public function isTimeout(): bool
    {
        return ($this->code == 28);
    }
}
