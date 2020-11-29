<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http;

/**
 * Util.
 *
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
        static $filter; $filter ??= function ($data) use (&$filter) {
            foreach ($data as $key => $value) {
                $data[$key] = is_array($value) ? $filter($value) : strval($value);
            }
            return $data;
        };

        $ret = http_build_query($filter($data));

        if ($normalizeArrays && strpos($qs, '%5D')) {
            $qs = str_replace(['%5B', '%5D'], ['[', ']'], $qs);
        }

        return $ret;
    }
}
