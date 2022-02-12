<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\request\Params;

/**
 * Param.
 *
 * @package froq\http\request
 * @object  froq\http\request\Param
 * @author  Kerem Güneş
 * @since   4.8
 * @static
 */
final class Param extends \StaticClass
{
    /**
     * Get one or many $_GET params, optionally mapping/filtering.
     *
     * @param  string|array|null $name
     * @param  mixed|null        $default
     * @param  callable|null     $map
     * @param  callable|null     $filter
     * @param  bool              $trim
     * @param  bool              $combine
     * @return mixed
     */
    public static function get(string|array $name = null, mixed $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false): mixed
    {
        return self::fetch('get', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Get one or many $_POST params, optionally mapping/filtering.
     *
     * @param  string|array|null $name
     * @param  mixed|null        $default
     * @param  callable|null     $map
     * @param  callable|null     $filter
     * @param  bool              $trim
     * @param  bool              $combine
     * @return mixed
     */
    public static function post(string|array $name = null, mixed $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false): mixed
    {
        return self::fetch('post', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Get one or many $_COOKIE params, optionally mapping/filtering.
     *
     * @param  string|array|null $name
     * @param  mixed|null        $default
     * @param  callable|null     $map
     * @param  callable|null     $filter
     * @param  bool              $trim
     * @param  bool              $combine
     * @return mixed
     */
    public static function cookie(string|array $name = null, mixed $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false): mixed
    {
        return self::fetch('cookie', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Fetch params from given source by name(s).
     */
    private static function fetch(string $source, string|array|null $name, mixed $default, callable|null $map, callable|null $filter, bool $trim, bool $combine): mixed
    {
        // All tick (* and null).
        $all = $name === '*' || $name === null;

        $names = $all ? null : (array) $name;
        $values = match ($source) {
            'get' => Params::gets($names, $default),
            'post' => Params::posts($names, $default),
            'cookie' => Params::cookies($names, $default),
        };

        if ($values) {
            // Trim is default for map, if not false.
            if ($trim && !$map) {
                $map = 'trim';
            }
            // Apply map & filter, if provided.
            if ($map || $filter) {
                $values = self::applyMapFilter($values, $map, $filter);
            }
        }

        if (!$all && $combine) {
            return array_combine($names, $values);
        }
        if (!$all && is_string($name)) {
            return array_first($values);
        }

        return $values;
    }

    /**
     * Apply map/filter.
     */
    private static function applyMapFilter(array $values, callable|null $map, callable|null $filter): array
    {
        // For safely mapping arrays/nulls.
        if ($map) {
            $map = fn($v) => self::map($map, $v);
        }

        $map    && $values = array_map($map, $values);
        $filter && $values = array_filter($values, $filter);

        return $values;
    }

    /**
     * Array/null safe map wrap, also recursive.
     */
    private static function map(callable $map, mixed $input): mixed
    {
        // Arrays.
        if (is_array($input)) {
            return array_map(
                fn($v) => self::map($map, $v),
                $input
            );
        }

        // Nulls, leave alone.
        if (is_null($input)) {
            return null;
        }

        // String otherwise, allways.
        return $map((string) $input);
    }
}
