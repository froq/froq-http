<?php
/**
 * MIT License <https://opensource.org/licenses/mit>
 *
 * Copyright (c) 2015 Kerem Güneş
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

namespace froq\http;

/**
 * Http.
 *
 * A static class that provides HTTP/1.0, HTTP/1.1 and HTTP/2.0 protocol versions both related
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
     * Versions.
     * @const string
     */
    public const VERSION_1_0     = 'HTTP/1.0',
                 VERSION_1_1     = 'HTTP/1.1',
                 VERSION_2_0     = 'HTTP/2.0';

    /**
     * Version default.
     * @const string
     */
    public const VERSION_DEFAULT = 'HTTP/1.1';

    /**
     * Date format (https://tools.ietf.org/html/rfc7231#section-7.1.1.2).
     * @const string
     */
    public const DATE_FORMAT = 'D, d M Y H:i:s \G\M\T';

    /**
     * Detect version.
     * @return string
     */
    public static function detectVersion(): string
    {
        return $_SERVER['SERVER_PROTOCOL'] ?? self::VERSION_DEFAULT;
    }

    /**
     * Parse version.
     * @param  string $version
     * @return float
     * @since  4.0
     */
    public static function parseVersion(string $version): float
    {
        if (strstr($version, 'HTTP/')) {
            $version = substr($version, 5, 3);
        }
        return (float) $version;
    }

    /**
     * Date.
     * @param  int|null $time
     * @return string
     * @since  4.0
     */
    public static function date(int $time = null): string
    {
        return gmdate(self::DATE_FORMAT, $time ?? time());
    }
}
