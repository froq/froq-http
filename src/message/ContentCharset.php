<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\message;

use StaticClass;

/**
 * Content Charset.
 *
 * @package froq\http\message
 * @object  froq\http\message\ContentCharset
 * @author  Kerem Güneş
 * @since   5.0 Derived from Body constants.
 * @static
 */
final class ContentCharset extends StaticClass
{
    /**
    * Names.
    * @const string
    */
    public const NA     = 'n/a',
                 UTF_8  = 'utf-8',
                 UTF_16 = 'utf-16';
}
