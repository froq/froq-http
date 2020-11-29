<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\request\Params;
use froq\common\objects\StaticClass;

/**
 * Param.
 *
 * @package froq\http\request
 * @object  froq\http\request\Param
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.8
 * @static
 */
final class Param extends StaticClass
{
    /**
     * Get.
     * @param  string|array  $name
     * @param  any|null      $valueDefault
     * @param  callable|null $map
     * @param  callable|null $filter
     * @return any
     */
    public static function get($name, $valueDefault = null, callable $map = null, callable $filter = null)
    {
        $values = Params::gets((array) $name, $valueDefault);
        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        return is_array($name) ? $values : ($values[0] ?? $valueDefault);
    }

    /**
     * Get.
     * @param  string|array  $name
     * @param  any|null      $valueDefault
     * @param  callable|null $map
     * @param  callable|null $filter
     * @return any
     */
    public static function post($name, $valueDefault = null, callable $map = null, callable $filter = null)
    {
        $values = Params::posts((array) $name, $valueDefault);
        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        return is_array($name) ? $values : ($values[0] ?? $valueDefault);
    }

    /**
     * Cookie.
     * @param  string|array  $name
     * @param  any|null      $valueDefault
     * @param  callable|null $map
     * @param  callable|null $filter
     * @return any
     */
    public static function cookie($name, $valueDefault = null, callable $map = null, callable $filter = null)
    {
        $values = Params::cookies((array) $name, $valueDefault);
        if ($map || $filter) {
            $values = self::applyMapFilter($values, $map, $filter);
        }

        return is_array($name) ? $values : ($values[0] ?? $valueDefault);
    }

    /**
     * Apply map/filter.
     * @param  array         $values
     * @param  callable|null $map
     * @param  callable|null $filter
     * @return array
     */
    private static function applyMapFilter(array $values, callable $map = null, callable $filter = null): array
    {
        // Apply map & filter if provided.
        $map && $values = array_map($map, $values);
        $filter && $values = array_filter($values, $filter);

        return $values;
    }
}
