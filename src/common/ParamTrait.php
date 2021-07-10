<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\request\Params;

/**
 * Param Trait.
 *
 * Represents a trait stack that used by Request object, utilizes accessing (to Request) params.
 *
 * @package  froq\http\common
 * @object   froq\http\common\ParamTrait
 * @author   Kerem Güneş
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait ParamTrait
{
    /**
     * Get one/many "GET" param.
     *
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     */
    public function get(string|array $name = null, $default = null)
    {
        return is_string($name) ? $this->getParam($name, $default)
                                : $this->getParams($name, $default);
    }

    /**
     * Get one "GET" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function getParam(string $name, $default = null)
    {
        return Params::get($name, $default);
    }

    /**
     * Get many "GET" param.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function getParams(array $names = null, $default = null): array
    {
        return Params::gets($names, $default);
    }

    /**
     * Check one/many "GET" param existence.
     *
     * @param  string|array<string>|null $name
     * @return bool
     */
    public function hasGet(string|array $name = null): bool
    {
        return is_string($name) ? $this->hasGetParam($name)
                                : $this->hasGetParams($name);
    }

    /**
     * Check one "GET" param existence.
     *
     * @param  string $name
     * @return bool
     */
    public function hasGetParam(string $name): bool
    {
        return Params::hasGet($name);
    }

    /**
     * Check many "GET" param existence.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasGetParams(array $names = null): bool
    {
        return Params::hasGets($names);
    }

    /**
     * Get one/many "POST" param.
     *
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     */
    public function post(string|array $name = null, $default = null)
    {
        return is_string($name) ? $this->postParam($name, $default)
                                : $this->postParams($name, $default);
    }

    /**
     * Get one "POST" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function postParam(string $name, $default = null)
    {
        return Params::post($name, $default);
    }

    /**
     * Get many "POST" param.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function postParams(array $names = null, $default = null): array
    {
        return Params::posts($names, $default);
    }

    /**
     * Check one/many "POST" param existence.
     *
     * @param  string|array<string>|null $name
     * @return bool
     */
    public function hasPost(string|array $name = null): bool
    {
        return is_string($name) ? $this->hasPostParam($name)
                                : $this->hasPostParams($name);
    }

    /**
     * Check one "POST" param existence.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPostParam(string $name): bool
    {
        return Params::hasPost($name);
    }

    /**
     * Check many "POST" param existence.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasPostParams(array $names = null): bool
    {
        return Params::hasPosts($names);
    }

    /**
     * Get one/many "COOKIE" param.
     *
     * @param  string|array<string>|null $name
     * @param  any|null                  $default
     * @return any|null
     */
    public function cookie(string|array $name = null, $default = null)
    {
        return is_string($name) ? $this->cookieParam($name, $default)
                                : $this->cookieParams($name, $default);
    }

    /**
     * Get one "COOKIE" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function cookieParam(string $name, $default = null)
    {
        return Params::cookie($name, $default);
    }

    /**
     * Get many "COOKIE" param.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public function cookieParams(array $names = null, $default = null): array
    {
        return Params::cookies($names, $default);
    }

    /**
     * Check one/many "COOKIE" param existence.
     *
     * @param  string|array<string>|null $name
     * @return bool
     */
    public function hasCookie(string|array $name = null): bool
    {
        return is_string($name) ? $this->hasCookieParam($name)
                                : $this->hasCookieParams($name);
    }

    /**
     * Get one "COOKIE" param existence.
     *
     * @param  string $name
     * @return bool
     */
    public function hasCookieParam(string $name): bool
    {
        return Params::hasCookie($name);
    }

    /**
     * Get many "COOKIE" param existence.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public function hasCookieParams(array $names = null): bool
    {
        return Params::hasCookies($names);
    }
}
