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
 * @package froq\http
 * @object  froq\http\Http
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final /* static */ class Http
{
    /**
     * Versions.
     * @const string
     */
    public const VERSION_1_0       = 'HTTP/1.0',
                 VERSION_1_1       = 'HTTP/1.1',
                 VERSION_2_0       = 'HTTP/2.0',
                 VERSION_DEFAULT   = self::VERSION_1_1;

    /**
     * Methods.
     * @conts string
     */
    public const METHOD_GET        = 'GET',
                 METHOD_POST       = 'POST',
                 METHOD_PUT        = 'PUT',
                 METHOD_PATCH      = 'PATCH',
                 METHOD_DELETE     = 'DELETE',
                 METHOD_OPTIONS    = 'OPTIONS',
                 METHOD_HEAD       = 'HEAD',
                 METHOD_TRACE      = 'TRACE',
                 METHOD_CONNECT    = 'CONNECT',
                 // non-standard
                 METHOD_COPY       = 'COPY',
                 METHOD_MOVE       = 'MOVE';

    /**
     * Detect version.
     * @return string
     */
    public static function detectVersion(): string
    {
        return ($_SERVER['SERVER_PROTOCOL'] ?? self::VERSION_DEFAULT);
    }
}
