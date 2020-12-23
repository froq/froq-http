<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\object\StaticClass;

/**
 * Params.
 *
 * @package froq\http\request
 * @object  froq\http\request\Params
 * @author  Kerem Güneş
 * @since   1.0
 * @static
 */
final class Params extends StaticClass
{
    /**
     * Get all params by GPC sort.
     *
     * @return array
     * @since  4.0
     */
    public static function all(): array
    {
        return [$_GET, $_POST, $_COOKIE];
    }

    /**
     * Get a "$_GET" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function get(string $name, $default = null)
    {
        return array_fetch($_GET, $name, $default);
    }

    /**
     * Get all/many "$_GET" params.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public static function gets(array $names = null, $default = null): array
    {
        return ($names === null) ? $_GET // All.
             : array_fetch($_GET, $names, $default);
    }

    /**
     * Check a "$_GET" param.
     *
     * @param  string $name
     * @return bool
     */
    public static function hasGet(string $name): bool
    {
        return self::get($name) !== null;
    }

    /**
     * Check all/many "$_GET" params.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasGets(array $names = null): bool
    {
        if ($names === null) {
            return !!$_GET;
        }

        foreach ($names as $name) {
            if (!self::hasGet($name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a "$_POST" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function post(string $name, $default = null)
    {
        return array_fetch($_POST, $name, $default);
    }

    /**
     * Get all/many "$_POST" params.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public static function posts(array $names = null, $default = null): array
    {
        return ($names === null) ? $_POST // All.
             : array_fetch($_POST, $names, $default);
    }

    /**
     * Check a "$_POST" param.
     *
     * @param  string $name
     * @return bool
     */
    public static function hasPost(string $name): bool
    {
        return self::post($name) !== null;
    }

    /**
     * Check all/many "$_POST" params.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasPosts(array $names = null): bool
    {
        if ($names === null) {
            return !!$_POST;
        }

        foreach ($names as $name) {
            if (!self::hasPost($name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a "$_COOKIE" param.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function cookie(string $name, $default = null)
    {
        return array_fetch($_COOKIE, $name, $default);
    }

    /**
     * Get all/many "$_COOKIE" params.
     *
     * @param  array<string>|null $names
     * @param  any|null           $default
     * @return array
     */
    public static function cookies(array $names = null, $default = null): array
    {
        return ($names === null) ? $_COOKIE // All.
             : array_fetch($_COOKIE, $names, $default);
    }

    /**
     * Check a "$_COOKIE" param.
     *
     * @param  string $name
     * @return bool
     */
    public static function hasCookie(string $name): bool
    {
        return self::cookie($name) !== null;
    }

    /**
     * Check all/many "$_COOKIE" params.
     *
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasCookies(array $names = null): bool
    {
        if ($names === null) {
            return !!$_COOKIE;
        }

        foreach ($names as $name) {
            if (!self::hasCookie($name)) {
                return false;
            }
        }
        return true;
    }
}
