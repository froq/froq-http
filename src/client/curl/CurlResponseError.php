<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client\curl;

use froq\http\client\curl\CurlError;

/**
 * Curl Response Error.
 *
 * Represents an error object that only thrown when client options "throwHttpErrors" is true.
 *
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\CurlResponseError
 * @author  Kerem Güneş
 * @since   5.0
 */
class CurlResponseError extends CurlError
{}
