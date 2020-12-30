<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\request\Params;
use froq\common\object\StaticClass;

/**
 * Param.
 *
 * @package froq\http\request
 * @object  froq\http\request\Param
 * @author  Kerem Güneş
 * @since   4.8
 * @static
 */
final class Param extends StaticClass
{
    /**
     * Get one or many "$_GET" params, optionally mapping/filtering.
     *
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $combine
     * @return any
     */
    public static function get(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $combine = false)
    {
        $values = Params::gets((array) $name, $default);

        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        if (is_array($name)) {
            $combine && $values = array_combine(
                $name, !$filter ? $values : array_pad($values, count($name), null)
            );

            return $values;
        }

        return $values[0] ?? $default;
    }

    /**
     * Get one or many "$_POST" params, optionally mapping/filtering.
     *
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $combine
     * @return any
     */
    public static function post(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $combine = false)
    {
        $values = Params::posts((array) $name, $default);

        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        if (is_array($name)) {
            $combine && $values = array_combine(
                $name, !$filter ? $values : array_pad($values, count($name), null)
            );

            return $values;
        }

        return $values[0] ?? $default;
    }

    /**
     * Get one or many "$_COOKIE" params, optionally mapping/filtering.
     *
     * @param  string|array  $name
     * @param  any|null      $default
     * @param  callable|null $map
     * @param  callable|null $filter
     * @param  bool          $combine
     * @return any
     */
    public static function cookie(string|array $name, $default = null, callable $map = null, callable $filter = null,
        bool $combine = false)
    {
        $values = Params::cookies((array) $name, $default);
        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        if (is_array($name)) {
            $combine && $values = array_combine(
                $name, !$filter ? $values : array_pad($values, count($name), null)
            );

            return $values;
        }

        return $values[0] ?? $default;
    }

    /**
     * Apply map/filter.
     *
     * @param  array         $values
     * @param  callable|null $map
     * @param  callable|null $filter
     * @return array
     * @internal
     */
    private static function applyMapFilter(array $values, callable $map = null, callable $filter = null): array
    {
        // Apply map & filter if provided.
        $map    && $values = array_map($map, $values);
        $filter && $values = array_filter($values, $filter);

        return $values;
    }
}
