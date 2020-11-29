<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\common\HeaderException;

/**
 * Header Trait.
 *
 * Represents a trait stack that used by both Request and Response objects, utilizes accessing (to
 * Request & Response) / modifying (of Response only) headers.
 *
 * @package  froq\http\common
 * @object   froq\http\common\HeaderTrait
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
        if (func_num_args() == 1) {
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
     * @param  string                    $name
     * @param  string|array<string>|null $value
     * @return self
     * @throws froq\http\common\HeaderException
     */
    public function addHeader(string $name, $value): self
    {
        if ($this->isRequest()) {
            throw new HeaderException('Cannot modify request headers');
        }

        // Memoize value check/convert function.
        static $valueOf;
        $valueOf ??= function ($name, $value) {
            if (is_null($value) || is_string($value)) {
                return $value;
            }

            if (is_scalar($value)) {
                return var_export($value, true);
            }

            throw new HeaderException('Non-null/scalar value given for "%s" header', [$name]);
        };

        if (is_array($value)) {
            foreach ($value as $valu) {
                $this->headers->add($name, $valueOf($name, $valu));
            }
        } else {
            $this->headers->add($name, $valueOf($name, $value));
        }

        return $this;
    }

    /**
     * Set header.
     * @param  string  $name
     * @param  ?string $value
     * @return self
     * @throws froq\http\common\HeaderException
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
     * @param  string                    $name
     * @param  string|array<string>|null $valueDefault
     * @return string|array<string>|null
     */
    public function getHeader(string $name, $valueDefault = null)
    {
        return $this->headers->get($name)
            ?? $this->headers->get(strtolower($name))
            ?? $valueDefault;
    }

    /**
     * Remove header.
     * @param  string $name
     * @param  bool   $defer
     * @return self
     * @throws froq\http\common\HeaderException
     */
    public function removeHeader(string $name, bool $defer = false): self
    {
        if ($this->isRequest()) {
            throw new HeaderException('Cannot modify request headers');
        }

        $header = $this->getHeader($name);
        if ($header != null) {
               $this->headers->remove($name)
            || $this->headers->remove(strtolower($name));

            // Remove instantly.
            if (!$defer) {
                $this->sendHeader($name, null);
            }
        }

        return $this;
    }
}
