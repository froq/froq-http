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
 * Response.
 * @return froq\http\Response
 */
function response(...$arguments): Response
{
    $response = app()->response();
    if ($arguments) {
        @ [$code, $content, $contentType, $contentCharset, $headers, $cookies] = $arguments;
        if ($code) $response->setStatus($code);
        if (!is_null($content)) {
            $response->setBody($content, $contentType, $contentCharset);
        }
        if ($headers) $response->setHeaders($headers);
        if ($cookies) $response->setCookies($cookies);
    }
    return $response;
}

/**
 * Uri.
 * @return froq\http\request\Uri
 */
function uri(): Uri
{
    return request()->uri();
}

/**
 * Status.
 * @param  int $code
 * @return void
 */
function status(int $code): void
{
    response()->setStatus($code);
}

/**
 * Is get.
 * @return bool
 */
function is_get(): bool
{
    return request()->method()->isGet();
}

/**
 * Is post.
 * @return bool
 */
function is_post(): bool
{
    return request()->method()->isPost();
}

/**
 * Is put.
 * @return bool
 */
function is_put(): bool
{
    return request()->method()->isPut();
}

/**
 * Is delete.
 * @return bool
 */
function is_delete(): bool
{
    return request()->method()->isDelete();
}

/**
 * Is ajax.
 * @return bool
 */
function is_ajax(): bool
{
    return request()->method()->isAjax();
}

/**
 * Get.
 * @param  string|array|null $name
 * @param  any|null          $value_default
 * @return any|null
 */
function get($name = null, $value_default = null)
{
    return is_null($name) || is_array($name)
        ? request()->getParams($name, $value_default)
        : request()->getParam($name, $value_default);
}

/**
 * Get has.
 * @param  string $name
 * @return bool
 */
function get_has(string $name): bool
{
    return array_key_exists($name, request()->getParams());
}

/**
 * Post.
 * @param  string|array|null $name
 * @param  any|null          $value_default
 * @return any|null
 */
function post($name = null, $value_default = null)
{
    return is_null($name) || is_array($name)
        ? request()->postParams($name, $value_default)
        : request()->postParam($name, $value_default);
}

/**
 * Post has.
 * @param  string $name
 * @return bool
 */
function post_has(string $name): bool
{
    return array_key_exists($name, request()->postParams());
}

/**
 * Cookie.
 * @param  string|array|null $name
 * @param  any|null          $value_default
 * @return any|null
 */
function cookie($name = null, $value_default = null)
{
    return is_null($name) || is_array($name)
        ? request()->cookieParams($name, $value_default)
        : request()->cookieParam($name, $value_default);
}

/**
 * Cookie has.
 * @param  string $name
 * @return bool
 */
function cookie_has(string $name): bool
{
    return array_key_exists($name, request()->cookieParams());
}

/**
 * Segment.
 * @param  int      $i
 * @param  any|null $value_default
 * @return any|null
 */
function segment(int $i, $value_default = null)
{
    return uri()->segment($i, $value_default);
}

/**
 * Segments.
 * @return array
 */
function segments(): array
{
    return uri()->segments();
}

/**
 * Segment param.
 * @param  int $i
 * @param  any|null $value_default
 * @return any|null
 */
function segment_argument(int $i, $value_default = null)
{
    return segment_arguments()[$i - 1] ?? $value_default;
}

/**
 * Segment arguments.
 * @return array
 */
function segment_arguments(): array
{
    return uri()->segmentArguments(app()->service()->isSite() ? 2 : 1);
}

/**
 * Redirect.
 * @param  string     $location
 * @param  int        $code
 * @param  array|null $headers
 * @param  array|null $cookies
 * @return void
 */
function redirect(string $location, int $code = Status::FOUND, array $headers = null, array $cookies = null): void
{
    response()->redirect($location, $code, $headers, $cookies);
}
