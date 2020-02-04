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

namespace froq\http\common\traits;

use froq\http\common\exceptions\HeaderException;

/**
 * Header Trait.
 *
 * Represents a trait stack that used by both Request and Response objects, utilizes accessing (to
 * Request & Response) / modifying (of Response only) headers.
 *
 * @package  froq\http\common\traits
 * @object   froq\http\common\traits\HeaderTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait HeaderTrait
{
    /**
     * Set/get/add header.
     * @param  string      $name
     * @param  string|null $value
     * @param  bool        $replace
     * @return self|array|null
     */
    public function header(string $name, string $value = null, bool $replace = true)
    {
        if ($value === null) {
            return $this->getHeader($name);
        }

        return $replace ? $this->setHeader($name, $value)
                        : $this->addHeader($name, $value);
    }

    /**
     * Has header.
     * @param  string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->headers->has($name)
            || $this->headers->has(strtolower($name));
    }

    /**
     * Add header.
     * @param  string  $name
     * @param  ?string $value
     * @return self
     * @throws froq\http\common\exceptions\HeaderException
     */
    public function addHeader(string $name, ?string $value): self
    {
        if ($this->isRequest()) {
            throw new HeaderException('Cannot modify request headers');
        }

        $this->headers->add($name, $value);

        return $this;
    }

    /**
     * Set header.
     * @param  string  $name
     * @param  ?string $value
     * @return self
     * @throws froq\http\common\exceptions\HeaderException
     */
    public function setHeader(string $name, ?string $value): self
    {
        if ($this->isRequest()) {
            throw new HeaderException('Cannot modify request headers');
        }

        $this->headers->set($name, $value);

        return $this;
    }

    /**
     * Get header.
     * @param  string      $name
     * @param  string|null $valueDefault
     * @return string|array|null
     */
    public function getHeader(string $name, string $valueDefault = null)
    {
        return $this->headers->get($name, $valueDefault)
            ?? $this->headers->get(strtolower($name), $valueDefault)
    }

    /**
     * Remove header.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     * @throws froq\http\common\exceptions\HeaderException
     */
    public function removeHeader(string $name, bool $defer = false): self
    {
        if ($this->isRequest()) {
            throw new HeaderException('Cannot modify request headers');
        }

        $header = $this->headers->get($name);
        if ($header != null) {
            $this->headers->remove($name);

            // Remove instantly.
            if (!$defer) {
                $this->sendHeader($name, null);
            }
        }

        return $this;
    }
}
