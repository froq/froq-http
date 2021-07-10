<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\exception\server;

use froq\http\exception\ServerException;
use froq\http\response\Status;

/**
 * Variant Also Negotiates Exception.
 *
 * @package froq\http\exception\server
 * @object  froq\http\exception\server\VariantAlsoNegotiatesException
 * @author  Kerem Güneş
 * @since   5.0
 */
class VariantAlsoNegotiatesException extends ServerException
{
    /** @const int */
    public const CODE = Status::VARIANT_ALSO_NEGOTIATES;
}
