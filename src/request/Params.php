<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\objects\StaticClass;

/**
 * Params.
 *
 * @package froq\http\request
 * @object  froq\http\request\Params
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 * @static
 */
final class Params extends StaticClass
{
    /**
     * All.
     * @return array
     * @since  4.0
     */
    public static function all(): array
    {
        return [$_GET, $_POST, $_COOKIE];
    }

    /**
     * Get.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function get(string $name, $default = null)
    {
        return array_fetch($_GET, $name, $default);
    }

    /**
     * Gets.
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
     * Has get.
     * @param  string $name
     * @return bool
     */
    public static function hasGet(string $name): bool
    {
        return self::get($name) !== null;
    }

    /**
     * Has gets.
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
     * Post.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function post(string $name, $default = null)
    {
        return array_fetch($_POST, $name, $default);
    }

    /**
     * Posts.
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
     * Has post.
     * @param  string $name
     * @return bool
     */
    public static function hasPost(string $name): bool
    {
        return self::post($name) !== null;
    }

    /**
     * Has posts.
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
     * Cookie.
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public static function cookie(string $name, $default = null)
    {
        return array_fetch($_COOKIE, $name, $default);
    }

    /**
     * Cookies.
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
     * Has cookie.
     * @param  string $name
     * @return bool
     */
    public static function hasCookie(string $name): bool
    {
        return self::cookie($name) !== null;
    }

    /**
     * Has cookies.
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
