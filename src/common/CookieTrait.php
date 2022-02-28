<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\common\CookieException;

/**
 * Cookie Trait.
 *
 * @package froq\http\common
 * @object  froq\http\common\CookieTrait
 * @author  Kerem Güneş
 * @since   4.0
 * @internal
 */
trait CookieTrait
{
    /**
     * Set/get a cookie.
     *
     * @param  string      $name
     * @param  string|null $value
     * @param  array|null  $options
     * @return self|array|null
     */
    public function cookie(string $name, string $value = null, array $options = null)
    {
        if (func_num_args() == 1) {
            return $this->getCookie($name);
        }

        return $this->setCookie($name, $value, $options);
    }

    /**
     * Check a cookie existence.
     *
     * @param  string $name
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return $this->cookies->has($name);
    }

    /**
     * Add a cookie.
     *
     * @alias of setCookie()
     */
    public function addCookie(...$args)
    {
        return $this->setCookie(...$args);
    }

    /**
     * Set a cookie.
     *
     * @param  string      $name
     * @param  string|null $value
     * @param  array|null  $options
     * @return self
     * @throws froq\http\common\CookieException
     */
    public function setCookie(string $name, string|null $value, array $options = null): self
    {
        if ($this->isRequest()) {
            throw new CookieException('Cannot modify request cookies');
        }

        $this->cookies->set($name, ['value' => $value, 'options' => $options]);

        return $this;
    }

    /**
     * Get a cookie.
     *
     * @param  string      $name
     * @param  string|null $default
     * @return string|null
     */
    public function getCookie(string $name, string $default = null): string|null
    {
        return $this->cookies->get($name, $default);
    }

    /**
     * Remove a cookie.
     *
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
        if ($cookie !== null) {
            $this->cookies->remove($name);

            // Remove instantly.
            $defer || $this->sendCookie($name, null, $cookie->toArray());
        } else {
            // Remove instantly.
            $defer || $this->sendCookie($name, null);
        }

        return $this;
    }
}
