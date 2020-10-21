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

namespace froq\http\request;

use froq\common\objects\StaticClass;
use froq\http\request\Params;

/**
 * Param.
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
