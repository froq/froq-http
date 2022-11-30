<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

/**
 * A static class that provides HTTP/1.0, HTTP/1.1 and HTTP/2.0 protocols & versions
 * and some HTTP related utility methods.
 *
 * @package froq\http
 * @object  froq\http\Http
 * @author  Kerem Güneş
 * @since   1.0
 * @static
 */
class Http extends \StaticClass
{
    /** @const string */
    public const PROTOCOL_1_0 = 'HTTP/1.0',
                 PROTOCOL_1_1 = 'HTTP/1.1',
                 PROTOCOL_2_0 = 'HTTP/2.0';

    /** @const string */
    public const DEFAULT_PROTOCOL = HTTP_DEFAULT_PROTOCOL;

    /** @const string */
    public const DATE_FORMAT = HTTP_DATE_FORMAT;

    /**
     * Get protocol.
     *
     * @return string
     * @since  5.0
     */
    public static function protocol(): string
    {
        return http_protocol();
    }

    /**
     * Get version.
     *
     * @return float
     * @since  5.0
     */
    public static function version(): float
    {
        return http_version();
    }

    /**
     * Format a time as HTTP date.
     *
     * @param  int|string|null $time
     * @return string
     * @since  4.0
     */
    public static function date(int|string $time = null): string
    {
        return http_date($time);
    }

    /**
     * Verify a date by HTTP format.
     *
     * @param  string $date
     * @return bool
     * @since  4.0
     */
    public static function dateVerify(string $date): bool
    {
        return http_date_verify($date);
    }
}
