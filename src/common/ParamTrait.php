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

namespace froq\http\common;

use froq\http\request\Params;
use froq\http\common\ParamException;

/**
 * Param Trait.
 *
 * Represents a trait stack that used by Request object, utilizes accessing (to Request) params.
 *
 * @package  froq\http\common
 * @object   froq\http\common\ParamTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait ParamTrait
{
    /**
     * Get.
     * @param  string|array|null $name
     * @param  any|null          $valueDefault
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function get($name = null, $valueDefault = null)
    {
        if (is_string($name)) {
            return $this->getParam($name, $valueDefault);
        }
        if ($name === null || is_array($name)) {
            return $this->getParams($name, $valueDefault);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Get param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function getParam(string $name, $valueDefault = null)
    {
        return Params::get($name, $valueDefault);
    }

    /**
     * Get params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function getParams(array $names = null, $valuesDefault = null): array
    {
        return Params::gets($names, $valuesDefault);
    }

    /**
     * Has get.
     * @param  string|array|null $name
     * @return bool
     * @throws froq\http\common\ParamException
     */
    public function hasGet($name = null): bool
    {
        if (is_string($name)) {
            return $this->hasGetParam($name);
        }
        if ($name === null || is_array($name)) {
            return $this->hasGetParams($name);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Has get param.
     * @param  string $name
     * @return bool
     */
    public function hasGetParam(string $name): bool
    {
        return Params::hasGet($name);
    }

    /**
     * Has get params.
     * @param  array|null $names
     * @return bool
     */
    public function hasGetParams(array $names = null): bool
    {
        return Params::hasGets($names);
    }

    /**
     * Post.
     * @param  string|array|null $name
     * @param  any|null          $valueDefault
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function post($name = null, $valueDefault = null)
    {
        if (is_string($name)) {
            return $this->postParam($name, $valueDefault);
        }
        if ($name === null || is_array($name)) {
            return $this->postParams($name, $valueDefault);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Post param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function postParam(string $name, $valueDefault = null)
    {
        return Params::post($name, $valueDefault);
    }

    /**
     * Post params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function postParams(array $names = null, $valuesDefault = null): array
    {
        return Params::posts($names, $valuesDefault);
    }

    /**
     * Has post.
     * @param  string|array|null $name
     * @return bool
     * @throws froq\http\common\ParamException
     */
    public function hasPost($name = null): bool
    {
        if (is_string($name)) {
            return $this->hasPostParam($name);
        }
        if ($name === null || is_array($name)) {
            return $this->hasPostParams($name);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Has post param.
     * @param  string $name
     * @return bool
     */
    public function hasPostParam(string $name): bool
    {
        return Params::hasPost($name);
    }

    /**
     * Has post params.
     * @param  array|null $names
     * @return bool
     */
    public function hasPostParams(array $names = null): bool
    {
        return Params::hasPosts($names);
    }

    /**
     * Cookie.
     * @param  string|array|null $name
     * @param  any|null          $valueDefault
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function cookie($name = null, $valueDefault = null)
    {
        if (is_string($name)) {
            return $this->cookieParam($name, $valueDefault);
        }
        if ($name === null || is_array($name)) {
            return $this->cookieParams($name, $valueDefault);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Cookie param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function cookieParam(string $name, $valueDefault = null)
    {
        return Params::cookie($name, $valueDefault);
    }

    /**
     * Cookie params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function cookieParams(array $names = null, $valuesDefault = null): array
    {
        return Params::cookies($names, $valuesDefault);
    }

    /**
     * Has cookie.
     * @param  string|array|null $name
     * @return bool
     * @throws froq\http\common\ParamException
     */
    public function hasCookie($name = null): bool
    {
        if (is_string($name)) {
            return $this->hasCookieParam($name);
        }
        if ($name === null || is_array($name)) {
            return $this->hasCookieParams($name);
        }

        throw new ParamException(sprintf('Invalid type %s for $name argument, string, array and '.
            'null accepted only', gettype($name)));
    }

    /**
     * Has cookie param.
     * @param  string $name
     * @return bool
     */
    public function hasCookieParam(string $name): bool
    {
        return Params::hasCookie($name);
    }

    /**
     * Has cookie params.
     * @param  array|null $names
     * @return bool
     */
    public function hasCookieParams(array $names = null): bool
    {
        return Params::hasCookies($names);
    }
}
