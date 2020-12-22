<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

/**
 * Payload Interface.
 *
 * Used by child classes only that derived from Payload object.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\PayloadInterface
 * @author  Kerem Güneş <k-gun@mail.com>
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
