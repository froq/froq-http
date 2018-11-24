<?php
/**
 * Copyright (c) 2015 Kerem Güneş
 *
 * MIT License <https://opensource.org/licenses/mit>
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
