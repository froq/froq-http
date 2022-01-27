<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\message;

/**
 * Content Type.
 *
 * @package froq\http\message
 * @object  froq\http\message\ContentType
 * @author  Kerem Güneş
 * @since   5.0 Derived from Body constants.
 * @static
 */
final class ContentType extends \StaticClass
{
    /** @const string */
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
                 IMAGE_WEBP               = 'image/webp',
                 IMAGE_PNG                = 'image/png',
                 IMAGE_GIF                = 'image/gif',
                 // Downloads.
                 APPLICATION_DOWNLOAD     = 'application/download',
                 APPLICATION_OCTET_STREAM = 'application/octet-stream';

    /**
     * Get text valid types.
     *
     * @return array
     * @since  6.0
     */
    public static function textTypes(): array
    {
        return [
            self::TEXT_HTML, self::TEXT_PLAIN,
            self::TEXT_XML, self::TEXT_JSON,
            self::APPLICATION_XML, self::APPLICATION_JSON
        ];
    }

    /**
     * Get image valid types.
     *
     * @return array
     * @since  6.0
     */
    public static function imageTypes(): array
    {
        return [
            self::IMAGE_JPEG, self::IMAGE_WEBP,
            self::IMAGE_PNG, self::IMAGE_GIF
        ];
    }

    /**
     * Get file valid types.
     *
     * @return array
     * @since  6.0
     */
    public static function fileTypes(): array
    {
        return [
            self::APPLICATION_OCTET_STREAM,
            self::APPLICATION_DOWNLOAD
        ];
    }
}
