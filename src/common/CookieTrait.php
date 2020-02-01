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

use froq\http\response\Cookie;
use froq\http\common\CookieException;

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
     * @param  string $name
     * @param  string $value
     * @return self|array|null
     */
    public function cookie(string $name, string $value = null)
    {
        if (func_num_args() == 1) {
            return $this->getCookie($name);
        }

        return $this->setCookie($name, $value);
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
     * @aliasOf setCookie()
     */
    public function addCookie(...$arguments)
    {
        return $this->setCookie(...$arguments);
    }

    /**
     * Set cookie.
     * @param  string     $name
     * @param  any|null   $value
     * @param  array|null $options
     * @return self
     * @throws froq\http\common\CookieException
     */
    public function setCookie(string $name, $value, array $options = null): self
    {
        if ($this->isRequest()) {
            throw new CookieException('Cannot modify request cookies');
        }

        // Check name.
        if (!preg_match('~^[a-z0-9_\-\.]+$~i', $name)) {
            throw new CookieException('Invalid cookie name '. $name);
        }

        $session = $this->app->session();
        if ($session != null && $session->getName() == $name) {
            throw new CookieException(sprintf('Invalid cookie name %s, name %s reserved as '.
                'session name', $name, $name));
        }

        if (is_array($value)) {
            [$value, $options] = Cookie::exportValueAndOptions($value);
        }

        $cookie = (
            $value instanceof Cookie
                ? $value : new Cookie($name, $value, $options)
        );
        $cookie->nameChecked = true; // Tick to sendCookie().

        $this->cookies->add($name, $cookie);

        return $this;
    }

    /**
     * Get cookie.
     * @param  string      $name
     * @param  string|null $valueDefault
     * @return string|null
     */
    public function getCookie(string $name, string $valueDefault = null)
    {
        return $this->cookies->get($name) ?? $valueDefault;
    }

    /**
     * Remove cookie.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     */
    public function removeCookie(string $name, bool $defer = false): self
    {
        if ($this->isRequest()) {
            throw new CookieException('Cannot modify request cookies');
        }

        $cookie = $this->cookies->get($name);
        if ($cookie != null) {
            $this->cookies->remove($name);

            // Remove instantly.
            if (!$defer) {
                $this->sendCookie($name, null, $cookie->toArray());
            }
        }

        return $this;
    }
}
