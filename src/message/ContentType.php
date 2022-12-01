<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http\message;

use froq\common\object\Enum;

/**
 * @package froq\http\message
 * @class   froq\http\message\ContentType
 * @author  Kerem Güneş
 * @since   5.0
 * @enum
 */
class ContentType extends Enum
{
    /** Not assigned. */
    public const NA = 'n/a';

    /** Texts. */
    public const TEXT_HTML = 'text/html', TEXT_PLAIN = 'text/plain', TEXT_XML = 'text/xml', TEXT_JSON = 'text/json',
                 APPLICATION_XML  = 'application/xml', APPLICATION_JSON = 'application/json';

    /** Images. */
    public const IMAGE_JPEG = 'image/jpeg', IMAGE_WEBP = 'image/webp', IMAGE_PNG = 'image/png',
                 IMAGE_GIF = 'image/gif';

    /** Files. */
    public const APPLICATION_DOWNLOAD = 'application/download',
                 APPLICATION_OCTET_STREAM = 'application/octet-stream';

    /**
     * Get text types.
     *
     * @return array
     * @since  6.0
     */
    public static function textTypes(): array
    {
        return [
            self::TEXT_HTML,       self::TEXT_PLAIN,
            self::TEXT_XML,        self::TEXT_JSON,
            self::APPLICATION_XML, self::APPLICATION_JSON
        ];
    }

    /**
     * Get image types.
     *
     * @return array
     * @since  6.0
     */
    public static function imageTypes(): array
    {
        return [
            self::IMAGE_JPEG, self::IMAGE_WEBP,
            self::IMAGE_PNG,  self::IMAGE_GIF
        ];
    }

    /**
     * Get file types.
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
