<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

/**
 * Util.
 *
 * @package froq\http
 * @object  froq\http\Util
 * @author  Kerem Güneş
 * @since   4.0
 * @static
 */
final class Util extends \StaticClass
{
    /**
     * Parse given URL.
     *
     * @param  string $url
     * @return array|null
     */
    public static function parseUrl(string $url): array|null
    {
        // Ensure scheme is http (or https).
        if (!str_starts_with($url, 'http')) {
            return null;
        }

        $parsedUrl = parse_url($url);
        if (empty($parsedUrl['host'])) {
            return null;
        }

        array_extract($parsedUrl,
            'scheme, host, port, user, pass, path, query, fragment',
            $scheme, $host, $port, $user, $pass, $path, $query, $fragment
        );

        $authority = '';
        if ($user !== null || $pass !== null) {
            $authority = $user;
            if ($pass !== null) {
                $authority .= ':' . $pass . '@';
            }
        }

        if ($port !== null) {
            $host .= ':' . $port;
        }

        $query ??= '';
        $path  ??= '/';

        parse_str($query, $query);

        // Base URL with scheme, host, authority and path.
        $url = sprintf('%s://%s%s%s', $scheme, $authority, $host, $path);

        return [$url, $query, $fragment, $parsedUrl];
    }

    /**
     * Parse given headers.
     *
     * @param  string $headers
     * @param  bool   $lower
     * @return array|null
     */
    public static function parseHeaders(string $headers, bool $lower = true): array|null
    {
        if (!$headers) {
            return null;
        }

        $headers = explode("\r\n", trim($headers));

        // Pull status line.
        $ret[0] = trim((string) array_shift($headers));

        foreach ($headers as $header) {
            $temp = explode(':', $header, 2);
            if (!isset($temp[0])) {
                continue;
            }

            $name  = trim((string) $temp[0]);
            $value = trim((string) $temp[1]);

            if ($lower) {
                $name = strtolower($name);
            }

            // Handle multi-headers.
            if (isset($ret[$name])) {
                $ret[$name] = array_merge((array) $ret[$name], [$value]);
            } else {
                $ret[$name] = $value;
            }
        }

        return $ret;
    }

    /**
     * Build a query string.
     *
     * @param  array $data
     * @param  bool  $normalize
     * @return string|null
     */
    public static function buildQuery(array $data, bool $normalize = true): string|null
    {
        if (!$data) {
            return null;
        }

        // Fix skipped nulls by http_build_query() & empty strings of falses.
        $data = array_map_recursive(fn($value) => is_bool($value) ? intval($value) : strval($value), $data);

        $ret = http_build_query($data);

        // Normalize arrays.
        if ($normalize && str_contains($ret, '%5D=')) {
            $ret = str_replace(['%5B', '%5D'], ['[', ']'], $ret);
        }

        return $ret;
    }

    /**
     * Build a header (line) string.
     *
     * @param  string      $name
     * @param  string|null $value
     * @return string|null
     * @since  6.0
     */
    public static function buildHeader(string $name, string|null $value): string|null
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return sprintf('%s: %s', $name, $value);
    }

    /**
     * Build a cookie string.
     *
     * @param  string      $name
     * @param  string|null $value
     * @param  array|null  $options
     * @return string|null
     * @since  6.0
     */
    public static function buildCookie(string $name, string|null $value, array $options = null): string|null
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $cookie = ['name' => $name, 'value' => $value] + array_replace(
            array_pad_keys([], ['expires', 'path', 'domain', 'secure', 'httponly', 'samesite']),
            array_map_keys($options ?? [], 'strtolower')
        );

        extract($cookie);

        $ret = rawurlencode($name) . '=';

        if ($value === '' || $value === null || $expires < 0) {
            $ret .= sprintf('n/a; Expires=%s; Max-Age=0', gmdate('D, d M Y H:i:s \G\M\T', 0));
        } else {
            $ret .= rawurlencode($value);

            // Must be given in-seconds format.
            if ($expires !== null) {
                $ret .= sprintf('; Expires=%s; Max-Age=%d', gmdate('D, d M Y H:i:s \G\M\T', time() + $expires),
                    $expires);
            }
        }

        $path     && $ret .= '; Path=' . $path;
        $domain   && $ret .= '; Domain=' . $domain;
        $secure   && $ret .= '; Secure';
        $httponly && $ret .= '; HttpOnly';
        $samesite && $ret .= '; SameSite=' . $samesite;

        return $ret;
    }
}
