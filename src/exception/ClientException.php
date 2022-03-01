<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\exception;

use froq\http\exception\trait\PrepareTrait;
use froq\http\HttpException;
use Throwable;

/**
 * Client Exception.
 *
 * @package froq\http\exception
 * @object  froq\http\exception\ClientException
 * @author  Kerem Güneş
 * @since   5.0
 */
class ClientException extends HttpException
{
    /** @see froq\http\exception\trait\PrepareTrait */
    use PrepareTrait;

    /**
     * Constructor.
     *
     * @param  string|null    $message
     * @param  mixed|null     $messageParams
     * @param  int|null       $code
     * @param  Throwable|null $previous
     * @param  Throwable|null $cause
     * @throws froq\http\HttpException
     */
    public function __construct(string $message = null, mixed $messageParams = null, int $code = null,
        Throwable $previous = null, Throwable $cause = null)
    {
        if ($code !== null) {
            // Forbid code assigns for internal classes.
            if (static::class != self::class && str_starts_with(static::class, __namespace__)) {
                throw new HttpException('Cannot set $code parameter for %s, it is already set internally',
                    static::class);
            }

            // Forbid invalid code assigns.
            if ($code < 400 || $code > 499) {
                throw new HttpException('Invalid client exception code %s, it must be between 400-499', $code);
            }
        }

        [$code, $message] = self::prepare($code, $message);

        parent::__construct($message, $messageParams, code: $code, previous: $previous, cause: $cause);
    }
}
