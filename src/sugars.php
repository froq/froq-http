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

use Froq\Http\Request\Uri;
use Froq\Http\Response\Status;

/**
 * Is get.
 * @return bool
 */
function is_get(): bool
{
    return app()->request()->method()->isGet();
}

/**
 * Is post.
 * @return bool
 */
function is_post(): bool
{
    return app()->request()->method()->isPost();
}

/**
 * Is put.
 * @return bool
 */
function is_put(): bool
{
    return app()->request()->method()->isPut();
}

/**
 * Is delete.
 * @return bool
 */
function is_delete(): bool
{
    return app()->request()->method()->isDelete();
}

/**
 * Is ajax.
 * @return bool
 */
function is_ajax(): bool
{
    return app()->request()->method()->isAjax();
}

/**
 * Get.
 * @param  string|null $name
 * @param  any|null    $value_default
 * @return any|null
 */
function get(string $name = null, $value_default = null)
{
    $request = app()->request();

    return ($name === null) ? $request->getParams() : $request->getParam($name, $value_default);
}

/**
 * Post.
 * @param  string|null $name
 * @param  any|null    $value_default
 * @return any|null
 */
function post(string $name = null, $value_default = null)
{
    $request = app()->request();

    return ($name === null) ? $request->postParams() : $request->postParam($name, $value_default);
}

/**
 * Cookie.
 * @param  string|null $name
 * @param  any|null    $value_default
 * @return any|null
 */
function cookie(string $name = null, $value_default = null)
{
    $request = app()->request();

    return ($name === null) ? $request->cookieParams() : $request->cookieParam($name, $value_default);
}

/**
 * Uri.
 * @return Froq\Http\Request\Uri
 */
function uri(): Uri
{
    return app()->request()->uri();
}

/**
 * Redirect.
 * @param  ... $args
 * @return void
 */
function redirect(...$args): void
{
    redirect_to(vsprintf(array_shift($args), $args));
}

/**
 * Redirect to.
 * @param  string $location
 * @param  int    $code
 * @return void
 */
function redirect_to(string $location, int $code = Status::FOUND): void
{
    app()->response()->redirect($location, $code);
}
