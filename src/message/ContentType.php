<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\message;

/**
 * Content Type.
 *
 * @package froq\http\message
 * @object  froq\http\message\ContentType
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   5.0
 * @static
 */
final class ContentType
{
    /**
    * Names.
    * @const string
    */
    public const NA                       = 'n/a',
                 // Texts.
                 TEXT_HTML                = 'text/html',
                 TEXT_PLAIN               = 'text/plain',
                 TEXT_XML                 = 'text/xml',
                 TEXT_JSON                = 'text/json',
                 APPLICATION_XML          = 'application/xml',
                 APPLICATION_JSON         = 'application/json',
                 // Images.
                 IMAGE_JPEG               = 'image/jpeg',
                 IMAGE_PNG                = 'image/png',
                 IMAGE_GIF                = 'image/gif',
                 IMAGE_WEBP               = 'image/webp',
                 // Downloads
                 APPLICATION_DOWNLOAD     = 'application/download',
                 APPLICATION_OCTET_STREAM = 'application/octet-stream';
}
