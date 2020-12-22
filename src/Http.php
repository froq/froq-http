<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
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
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 * @static
 */
final class Http
{
    /**
     * Protocols.
     * @const string
     */
    public const PROTOCOL_1_0     = 'HTTP/1.0',
                 PROTOCOL_1_1     = 'HTTP/1.1',
                 PROTOCOL_2_0     = 'HTTP/2.0';

    /**
     * Version default.
     * @const string
     */
    public const PROTOCOL_DEFAULT = self::PROTOCOL_1_1;

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
        return ($_SERVER['SERVER_PROTOCOL'] ?? self::PROTOCOL_DEFAULT);
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
     * @param  int|null $time
     * @return string
     * @since  4.0
     */
    public static function date(int $time = null): string
    {
        return gmdate(self::DATE_FORMAT, $time ?? time());
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
