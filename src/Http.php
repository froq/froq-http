<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

/**
 * Http.
 *
 * Represents a static class that provides HTTP/1.0, HTTP/1.1 and HTTP/2.0 protocol versions both related
 * utility methods.
 *
 * @package froq\http
 * @object  froq\http\Http
 * @author  Kerem Güneş
 * @since   1.0
 * @static
 */
final class Http
{
    /**
     * Protocols.
     * @const string
     */
    public const PROTOCOL_1_0 = 'HTTP/1.0',
                 PROTOCOL_1_1 = 'HTTP/1.1',
                 PROTOCOL_2_0 = 'HTTP/2.0';

    /**
     * Default protocol.
     * @const string
     */
    public const DEFAULT_PROTOCOL = 'HTTP/1.1';

    /**
     * Date format (https://tools.ietf.org/html/rfc7231#section-7.1.1.2).
     * @const string
     */
    public const DATE_FORMAT = 'D, d M Y H:i:s \G\M\T';

    /**
     * Get protocol.
     *
     * @return string
     * @since  5.0 Derived from version().
     */
    public static function protocol(): string
    {
        return ($_SERVER['SERVER_PROTOCOL'] ?? self::DEFAULT_PROTOCOL);
    }

    /**
     * Get version.
     *
     * @return float
     * @since  5.0 Changed to float return.
     */
    public static function version(): float
    {
        return (float) substr(self::protocol(), 5, 3);
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
        $time ??= time();

        if (is_string($time)) {
            $time = strtotime($time);
        }

        return gmdate(self::DATE_FORMAT, $time);
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
        return ($d = date_create_from_format(self::DATE_FORMAT, $date))
            && ($d->format(self::DATE_FORMAT) === $date);
    }
}
