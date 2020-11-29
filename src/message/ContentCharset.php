<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\message;

/**
 * Content Charset.
 *
 * @package froq\http\message
 * @object  froq\http\message\ContentCharset
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   5.0
 * @static
 */
final class ContentCharset
{
    /**
    * Names.
    * @const string
    */
    public const NA     = 'n/a',
                 UTF_8  = 'utf-8',
                 UTF_16 = 'utf-16';
}
