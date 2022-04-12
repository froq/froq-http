<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response\payload;

/**
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\PayloadInterface
 * @author  Kerem Güneş
 * @since   4.0
 */
interface PayloadInterface
{
    /**
     * Handle.
     *
     * @return string|resource|GdImage|null
     * @throws froq\http\response\payload\PayloadException
     */
    public function handle();
}
