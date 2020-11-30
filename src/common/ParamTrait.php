<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\common\ParamException;
use froq\http\request\Params;

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
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function get($name = null, $default = null)
    {
        if (is_string($name)) {
            return $this->getParam($name, $default);
        }
        if ($name === null || is_array($name)) {
            return $this->getParams($name, $default);
        }

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
    }

    /**
     * Get param.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function getParam(string $name, $default = null)
    {
        return Params::get($name, $default);
    }

    /**
     * Get params.
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function getParams(array $names = null, $default = null): array
    {
        return Params::gets($names, $default);
    }

    /**
     * Has get.
     * @param  string|array<string>|null $name
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

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
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
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasGetParams(array $names = null): bool
    {
        return Params::hasGets($names);
    }

    /**
     * Post.
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function post($name = null, $default = null)
    {
        if (is_string($name)) {
            return $this->postParam($name, $default);
        }
        if ($name === null || is_array($name)) {
            return $this->postParams($name, $default);
        }

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
    }

    /**
     * Post param.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function postParam(string $name, $default = null)
    {
        return Params::post($name, $default);
    }

    /**
     * Post params.
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function postParams(array $names = null, $default = null): array
    {
        return Params::posts($names, $default);
    }

    /**
     * Has post.
     * @param  string|array<string>|null $name
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

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
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
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasPostParams(array $names = null): bool
    {
        return Params::hasPosts($names);
    }

    /**
     * Cookie.
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     * @throws froq\http\common\ParamException
     */
    public function cookie($name = null, $default = null)
    {
        if (is_string($name)) {
            return $this->cookieParam($name, $default);
        }
        if ($name === null || is_array($name)) {
            return $this->cookieParams($name, $default);
        }

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
    }

    /**
     * Cookie param.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function cookieParam(string $name, $default = null)
    {
        return Params::cookie($name, $default);
    }

    /**
     * Cookie params.
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function cookieParams(array $names = null, $default = null): array
    {
        return Params::cookies($names, $default);
    }

    /**
     * Has cookie.
     * @param  string|array<string>|null $name
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

        throw new ParamException("Invalid type '%s' for \$name argument, valids are: string, ".
            "array<string>, null", gettype($name));
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
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasCookieParams(array $names = null): bool
    {
        return Params::hasCookies($names);
    }
}
