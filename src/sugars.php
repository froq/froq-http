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

use froq\http\request\Uri;
use froq\http\response\Status;
use froq\http\{Request, Response};

/**
 * Request.
 * @return froq\http\Request
 */
function request(): Request
{
    return app()->request();
}

/**
 * Request uri.
 * @return froq\http\request\Uri
 */
function request_uri(): Uri
{
    return app()->request()->uri();
}

/**
 * Request header.
 * @param  string  $name
 * @param  ?string $value_default
 * @return ?string
 */
function request_header(string $name, ?string $value_default = null): ?string
{
    return app()->request()->getHeader($name, $value_default);
}

/**
 * Response.
 * @return froq\http\Response
 */
function response(): Response
{
    return app()->response();
}

/**
 * Response status.
 * @param  int $code
 * @return void
 */
function response_status(int $code): void
{
    app()->response()->setStatus($code);
}

/**
 * Response header.
 * @param  string $name
 * @param  string $value
 * @return void
 */
function response_header(string $name, ?string $value): void
{
    app()->response()->setHeader($name, $value);
}

/**
 * Response cookie.
 * @param  string $name
 * @param  string $value
 * @return void
 */
function response_cookie(string $name, ?string $value, int $expire = 0,
        string $path = '/', string $domain = '', bool $secure = false, bool $http_only = false): void
{
    app()->response()->setCookie($name, $value, $expire, $path, $domain, $secure, $http_only);
}

/**
 * Response body.
 * @param  any         $body
 * @param  string|null $content_type
 * @param  string|null $content_charset
 * @return void
 */
function response_body($body, string $content_type = null, string $content_charset = null): void
{
    app()->response()->setBody($body, $content_type, $content_charset);
}

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
 * Get has.
 * @param  string $name
 * @return bool
 */
function get_has(string $name): bool
{
    return array_key_exists($name, app()->request()->getParams());
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
 * Post has.
 * @param  string $name
 * @return bool
 */
function post_has(string $name): bool
{
    return array_key_exists($name, app()->request()->postParams());
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
 * Cookie has.
 * @param  string $name
 * @return bool
 */
function cookie_has(string $name): bool
{
    return array_key_exists($name, app()->request()->cookieParams());
}

/**
 * Uri.
 * @alias of request_uri()
 */
function uri(): Uri
{
    return app()->request()->uri();
}

/**
 * Status.
 * @alias of response_status()
 */
function status(int $code): void
{
    app()->response()->setStatus($code);
}

/**
 * Segment.
 * @param  int      $i
 * @param  any|null $value_default
 * @return any|null
 */
function segment(int $i, $value_default = null)
{
    return app()->request()->uri()->segment($i, $value_default);
}

/**
 * Segments.
 * @return array
 */
function segments(): array
{
    return app()->request()->uri()->segments();
}

/**
 * Segment param.
 * @param  int $i
 * @param  any $value_default
 * @return any|null
 */
function segment_param(int $i, $value_default = null)
{
    return segment_params()[$i - 1] ?? $value_default;
}

/**
 * Segment params.
 * @return array
 */
function segment_params()
{
    return app()->request()->uri()->segmentArguments(app()->service()->isSite() ? 2 : 1);
}

/**
 * Redirect.
 * @param  ... $arguments
 * @return void
 */
function redirect(...$arguments): void
{
    redirect_to(vsprintf(array_shift($arguments), $arguments));
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
