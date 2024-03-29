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
 * @package froq\http\exception\client
 * @object  froq\http\exception\client\UnsupportedMediaTypeException
 * @author  Kerem Güneş
 * @since   5.0
 */
class UnsupportedMediaTypeException extends ClientException
{
    /** @const int */
    public final const CODE = Status::UNSUPPORTED_MEDIA_TYPE;
}
