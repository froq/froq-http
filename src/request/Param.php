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
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $trim
     * @param  bool          $combine
     * @return any
     */
    public static function get(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false)
    {
        return self::fetch('get', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Get one or many $_POST params, optionally mapping/filtering.
     *
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $trim
     * @param  bool          $combine
     * @return any
     */
    public static function post(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false)
    {
        return self::fetch('post', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Get one or many $_COOKIE params, optionally mapping/filtering.
     *
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $trim
     * @param  bool          $combine
     * @return any
     */
    public static function cookie(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $trim = true, bool $combine = false)
    {
        return self::fetch('cookie', $name, $default, $map, $filter, $trim, $combine);
    }

    /**
     * Fetch params from given source by name(s).
     */
    private static function fetch(string $source, string|array $name, mixed $default, callable|null $map, callable|null $filter,
        bool $trim, bool $combine)
    {
        $all = ($name === '*');

        // If all entries wanted (* and null => all).
        $names = !$all ? (array) $name : null;

        $values = match ($source) {
            'get'    => Params::gets($names, $default),
            'post'   => Params::posts($names, $default),
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
        if ($map) $map = fn($v) => self::wrapMap($v, $map);

        $map    && $values = array_map($map, $values);
        $filter && $values = array_filter($values, $filter);

        return $values;
    }

    /**
     * Array/null safe map wrap.
     * @since 5.0
     */
    private static function wrapMap($input, $map)
    {
        return is_array($input)
             ? array_map(fn($v) => self::wrapMap($v, $map), $input)
             : ($input !== null ? $map((string) $input) : $input);
             // $map((string) $in); // Always string. @nope
    }
}
