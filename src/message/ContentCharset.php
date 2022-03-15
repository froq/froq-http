<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\message;

use froq\encoding\Encoding;

/**
 * Content Charset.
 *
 * @package froq\http\message
 * @object  froq\http\message\ContentCharset
 * @author  Kerem Güneş
 * @since   5.0
 * @static
 */
final class ContentCharset extends Encoding
{
    /**
     * Not assigned.
     * @const string
     */
    public const NA = 'n/a';
}
