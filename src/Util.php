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
 * Util.
 * @package froq\http
 * @object  froq\http\Util
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 * @static
 */
final class Util
{
    /**
     * Parse url.
     * @param  string $url
     * @return ?array
     */
    public static function parseUrl(string $url): ?array
    {
        // Ensure scheme is http (or https).
        if (strpos($url, 'http') !== 0) {
            return null;
        }

        $parsedUrl = parse_url($url);
        if (empty($parsedUrl['host'])) {
            return null;
        }

        @ [$authority, $user, $pass] = ['', $parsedUrl['user'], $parsedUrl['pass']];
        if ($user != null || $pass != null) {
            $authority = $user;
            if ($pass != null) {
                $authority .= ':'. $pass .'@';
            }
        }

        $host = $parsedUrl['host'];
        $port = $parsedUrl['port'] ?? null;
        if ($port != null) {
            $host .= ':'. $port;
        }

        $query = $parsedUrl['query'] ?? null;
        if ($query != null) {
            parse_str($query, $query);
        }

        // Base URL with scheme, host, authority and path.
        $url = sprintf('%s://%s%s%s', $parsedUrl['scheme'], $authority, $host,
            $parsedUrl['path'] ?? '/');

        $urlParams = $query;
        $urlFragment = $parsedUrl['fragment'] ?? null;

        return [$url, $urlParams, $urlFragment, $parsedUrl];
    }

    /**
     * Parse headers.
     * @param  string    $headers
     * @param  bool|null $lower
     * @return array
     */
    public static function parseHeaders(string $headers, bool $lower = null): array
    {
        $ret = [];

        $headers = explode("\r\n", trim($headers));
        if ($headers != null) {
            // Pick status line.
            $ret[0] = trim(array_shift($headers));

            foreach ($headers as $header) {
                @ [$name, $value] = explode(':', $header, 2);
                if ($name === null) {
                    continue;
                }

                $name = trim((string) $name);
                $value = trim((string) $value);
                if ($lower) {
                    $name = strtolower($name);
                }

                // Handle multi-headers as array.
                if (isset($ret[$name])) {
                    $ret[$name] = array_merge((array) $ret[$name], [$value]);
                } else {
                    $ret[$name] = $value;
                }
            }
        }

        return $ret;
    }

    /**
     * Build query.
     * @param  array $data
     * @param  bool  $normalizeArrays
     * @return string
     */
    public static function buildQuery(array $data, bool $normalizeArrays = true): string
    {
        // Memoize: fix skipped NULL values by http_build_query().
        static $filter; if (!$filter) {
               $filter = function ($data) use (&$filter) {
                    foreach ($data as $key => $value) {
                        $data[$key] = is_array($value) ? $filter($value) : strval($value);
                    }
                    return $data;
               };
        };

        $ret = http_build_query($filter($data));

        if ($normalizeArrays) {
            $ret = preg_replace('~([\w\.\-]+)%5B([\w\.\-]+)%5D(=)?~iU', '\1[\2]\3', $ret);
        }

        return $ret;
    }
}
