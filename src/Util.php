<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\common\object\StaticClass;

/**
 * Util.
 *
 * @package froq\http
 * @object  froq\http\Util
 * @author  Kerem Güneş
 * @since   4.0
 * @static
 */
final class Util extends StaticClass
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

        [$authority, $user, $pass] = ['', $parsedUrl['user'] ?? null, $parsedUrl['pass'] ?? null];
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

        $query    = $parsedUrl['query'] ?? null;
        $fragment = $parsedUrl['fragment'] ?? null;

        parse_str((string) $query, $query);

        // Base URL with scheme, host, authority and path.
        $url = sprintf('%s://%s%s%s', $parsedUrl['scheme'], $authority, $host,
            $parsedUrl['path'] ?? '/');

        return [$url, $query, $fragment, $parsedUrl];
    }

    /**
     * Parse given headers.
     *
     * @param  string $headers
     * @param  bool   $lower
     * @return array
     */
    public static function parseHeaders(string $headers, bool $lower = true): array
    {
        $headers = explode("\r\n", trim($headers));

        // Pick status line.
        $ret[0] = trim((string) array_shift($headers));

        foreach ($headers as $header) {
            @ [$name, $value] = explode(':', $header, 2);
            if ($name === null) {
                error_clear(2);
                continue;
            }

            $name  = trim((string) $name);
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

        return $ret;
    }

    /**
     * Build a query string.
     *
     * @param  array $data
     * @param  bool  $normalizeArrays
     * @return string
     */
    public static function buildQuery(array $data, bool $normalizeArrays = true): string
    {
        $ret = http_build_query(
            // Fix skipped NULL values by http_build_query().
            array_map_recursive($data, 'strval')
        );

        if ($normalizeArrays && str_contains($ret, '%5D=')) {
            $ret = str_replace(['%5B', '%5D'], ['[', ']'], $ret);
        }

        return $ret;
    }
}
