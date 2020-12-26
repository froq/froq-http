<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\exception\client;

use froq\http\exception\ClientException;
use froq\http\response\Status;

/**
 * URI Too Long Exception.
 *
 * @package froq\http\exception\client
 * @object  froq\http\exception\client\UriTooLongException
 * @author  Kerem Güneş
 * @since   5.0
 */
class UriTooLongException extends ClientException
{
    /** @const int */
    public const CODE = Status::URI_TOO_LONG;
}
