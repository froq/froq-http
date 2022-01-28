<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\exception\trait;

use froq\http\response\Status;

/**
 * Prepare Trait.
 *
 * Used for code & message preparation on Client & Server exceptions internally.
 *
 * @package froq\http\exception\trait
 * @object  froq\http\exception\trait\PrepareTrait
 * @author  Kerem Güneş
 * @since   5.0
 * @internal
 */
trait PrepareTrait
{
    /**
     * Prepare.
     *
     * @param  int|null    $code
     * @param  string|null $message
     * @return array
     */
    public static final function prepare(int|null $code, string|null $message): array
    {
        // Overwrite on code with child class code.
        if (defined(static::class . '::CODE')) {
            $code = static::CODE;
        }

        $message ??= ($code >= 400) ? Status::getTextByCode($code) : null;
        $message && $message = ucfirst(strtolower($message));

        return [$code, $message];
    }
}
