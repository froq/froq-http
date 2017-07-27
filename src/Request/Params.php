<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Froq\Http\Request;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\Params
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Params
{
    /**
     * Stack.
     * @var array
     */
    private $stack = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->stack['get'] = $_GET;
        $this->stack['post'] = $_POST;
        $this->stack['cookie'] = $_COOKIE;
    }

    /**
     * Get.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function get(string $name, $valueDefault = null)
    {
        return $this->stack['get'][$name] ?? $valueDefault;
    }

    /**
     * Gets.
     * @return array
     */
    public function gets(): array
    {
        return $this->stack['get'];
    }

    /**
     * Post.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function post(string $name, $valueDefault = null)
    {
        return $this->stack['post'][$name] ?? $valueDefault;
    }

    /**
     * Posts.
     * @return array
     */
    public function posts(): array
    {
        return $this->stack['post'];
    }

    /**
     * Cookie.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function cookie(string $name, $valueDefault = null)
    {
        return $this->stack['cookie'][$name] ?? $valueDefault;
    }

    /**
     * Cookies.
     * @return array
     */
    public function cookies(): array
    {
        return $this->stack['cookie'];
    }

    /**
     * To array.
     * @param  string|null $key
     * @return array.
     */
    public function toArray(string $key = null): array
    {
        return ($key != null) ? $this->stack[strtolower($key)] : $this->stack;
    }
}
