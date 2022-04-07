<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\common\Exception;
use froq\http\response\Status;

/**
 * Http Exception.
 *
 * @package froq\http
 * @object  froq\http\HttpException
 * @author  Kerem Güneş
 * @since   1.0
 */
class HttpException extends Exception
{
    /**
     * Prepare code & message for subclasses.
     *
     * @param  ?int    $code
     * @param  ?string $message
     * @return array
     * @since  5.0, 6.0
     */
    public static final function prepare(?int $code, ?string $message): array
    {
        // Overwrite on code with child class code.
        if (defined(static::class . '::CODE')) {
            $code = static::CODE;
        }

        if ($code >= 400 && $message === null) {
            $message = Status::getTextByCode($code);
            $message && $message = ucfirst(strtolower($message));
        }

        return [$code, $message];
    }
}
