<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\objects\StaticClass;
use froq\util\Arrays;

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
     * @param  any|null $valueDefault
     * @return any|null
     */
    public static function get(string $name, $valueDefault = null)
    {
        return Arrays::get($_GET, $name, $valueDefault);
    }

    /**
     * Gets.
     * @param  array<string>|null $names
     * @param  any|null           $valuesDefault
     * @return array
     */
    public static function gets(array $names = null, $valuesDefault = null): array
    {
        return ($names === null) ? $_GET // All.
             : Arrays::getAll($_GET, $names, $valuesDefault);
    }

    /**
     * Has get.
     * @param  string $name
     * @return bool
     */
    public static function hasGet(string $name): bool
    {
        return isset($_GET[$name]);
    }

    /**
     * Has gets.
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasGets(array $names = null): bool
    {
        if ($names == null) {
            return !empty($_GET);
        }

        foreach ($names as $name) {
            if (!isset($_GET[$name])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Post.
     * @param  string   $name
     * @param  any|null $valueDefault
     * @return any|null
     */
    public static function post(string $name, $valueDefault = null)
    {
        return Arrays::get($_POST, $name, $valueDefault);
    }

    /**
     * Posts.
     * @param  array<string>|null $names
     * @param  any|null           $valuesDefault
     * @return array
     */
    public static function posts(array $names = null, $valuesDefault = null): array
    {
        return ($names === null) ? $_POST // All.
             : Arrays::getAll($_POST, $names, $valuesDefault);
    }

    /**
     * Has post.
     * @param  string $name
     * @return bool
     */
    public static function hasPost(string $name): bool
    {
        return isset($_POST[$name]);
    }

    /**
     * Has posts.
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasPosts(array $names = null): bool
    {
        if ($names == null) {
            return !empty($_POST);
        }

        foreach ($names as $name) {
            if (!isset($_POST[$name])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Cookie.
     * @param  string   $name
     * @param  any|null $valueDefault
     * @return any|null
     */
    public static function cookie(string $name, $valueDefault = null)
    {
        return Arrays::get($_COOKIE, $name, $valueDefault);
    }

    /**
     * Cookies.
     * @param  array<string>|null $names
     * @param  any|null           $valuesDefault
     * @return array
     */
    public static function cookies(array $names = null, $valuesDefault = null): array
    {
        return ($names === null) ? $_COOKIE // All.
             : Arrays::getAll($_COOKIE, $names, $valuesDefault);
    }

    /**
     * Has cookie.
     * @param  string $name
     * @return bool
     */
    public static function hasCookie(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Has cookies.
     * @param  array<string>|null $names
     * @return bool
     */
    public static function hasCookies(array $names = null): bool
    {
        if ($names == null) {
            return !empty($_COOKIE);
        }

        foreach ($names as $name) {
            if (!isset($_COOKIE[$name])) {
                return false;
            }
        }
        return true;
    }
}
