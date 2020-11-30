<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\common\CookieException;
use froq\http\response\Cookie;

/**
 * Cookie Trait.
 *
 * Represents a trait stack that used by both Request and Response objects, utilizes accessing (to
 * Request & Response) / modifying (of Response only) cookies.
 *
 * @package  froq\http\common
 * @object   froq\http\common\CookieTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait CookieTrait
{
    /**
     * Set/get cookie.
     * @param  string      $name
     * @param  scalar|null $value
     * @param  array|null  $options
     * @return self|array|null
     */
    public function cookie(string $name, $value = null, array $options = null)
    {
        if (func_num_args() == 1) {
            return $this->getCookie($name);
        }
        return $this->setCookie($name, $value, $options);
    }

    /**
     * Has cookie.
     * @param  string $name
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return $this->cookies->has($name);
    }

    /**
     * Add cookie.
     * @alias of setCookie()
     */
    public function addCookie(...$_)
    {
        return $this->setCookie(...$_);
    }

    /**
     * Set cookie.
     * @param  string     $name
     * @param  ?scalar    $value
     * @param  array|null $options
     * @return self
     * @throws froq\http\common\CookieException
     */
    public function setCookie(string $name, $value, array $options = null): self
    {
        if ($this->isRequest()) {
            throw new CookieException('Cannot modify request cookies');
        }

        $this->cookies->set($name, new Cookie($name, $value, $options));

        return $this;
    }

    /**
     * Get cookie.
     * @param  string      $name
     * @param  string|null $valueDefault
     * @return ?string
     */
    public function getCookie(string $name, string $valueDefault = null): ?string
    {
        return $this->cookies->get($name, $valueDefault);
    }

    /**
     * Remove cookie.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     * @throws froq\http\common\CookieException
     */
    public function removeCookie(string $name, bool $defer = false): self
    {
        if ($this->isRequest()) {
            throw new CookieException('Cannot modify request cookies');
        }

        $cookie = $this->getCookie($name);
        if ($cookie != null) {
            $this->cookies->remove($name);

            // Remove instantly.
            if (!$defer) {
                $this->sendCookie($name, null, $cookie->toArray());
            }
        } else {
            // Remove instantly.
            if (!$defer) {
                $this->sendCookie($name, null);
            }
        }

        return $this;
    }
}
