<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\common\interface\{Listable, Arrayable, Objectable, Stringable};
use froq\common\trait\{DataCountTrait, DataEmptyTrait, DataToListTrait, DataToArrayTrait};
use froq\collection\trait\{EachTrait, FilterTrait, MapTrait, GetTrait};
use froq\util\Util;

/**
 * Url Query.
 *
 * An array-like class for working with URL-queries in OOP-style.
 *
 * @package froq\http
 * @object  froq\http\UrlQuery
 * @author  Kerem Güneş
 * @since   5.1
 */
final class UrlQuery implements Listable, Arrayable, Objectable, Stringable, \Countable, \ArrayAccess
{
    /**
     * @see froq\common\trait\DataCountTrait
     * @see froq\common\trait\DataEmptyTrait
     * @see froq\common\trait\DataToListTrait
     * @see froq\common\trait\DataToArrayTrait
     */
    use DataCountTrait, DataEmptyTrait, DataToListTrait, DataToArrayTrait;

    /**
     * @see froq\collection\trait\EachTrait
     * @see froq\collection\trait\FilterTrait
     * @see froq\collection\trait\MapTrait
     * @see froq\collection\trait\GetTrait
     */
    use EachTrait, FilterTrait, MapTrait, GetTrait;

    /** @var array */
    private array $data = [];

    /**
     * Constructor.
     *
     * @param array|string $data
     */
    public function __construct(array|string $data)
    {
        $this->data = is_array($data) ? self::mapData($data)
            : Util::parseQueryString($data);
    }

    /** @magic */
    public function __debugInfo(): array
    {
        return $this->data;
    }

    /** @magic */
    public function __set(string $key, string|null $value): void
    {
        $this->set($key, $value);
    }

    /** @magic */
    public function __get(string $key): string|null
    {
        return $this->get($key);
    }

    /**
     * Check whether a key is set & not null.
     *
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Check whether a key is set.
     *
     * @param  string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Check whether a key is set & not empty "" / null (usable for dotted notations) and
     * set ref'ed value with fetched value.
     *
     * @param  string       $key
     * @param  string|null &$value
     * @return bool
     */
    public function hasValue(string $key, string|null &$value = null): bool
    {
        $value = $this->get($key);

        return ($value !== null && $value !== '');
    }

    /**
     * Add an item.
     *
     * @param string       $key
     * @param string|array $value
     * @return self
     * @since  6.0
     */
    public function add(string $key, string|array $value): self
    {
        if (is_array($value)) {
            $value = self::mapData($value);
        }

        if (isset($this->data[$key])) {
            $this->data[$key] = array_concat([], $this->data[$key], $value);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Add many items.
     *
     * @param  array<string, string> $data
     * @return self
     * @since  6.0
     */
    public function addAll(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Set an item.
     *
     * @param string $key
     * @param string $value
     * @return self
     * @since  6.0
     */
    public function set(string $key, string $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Set many items.
     *
     * @param  array<string, string> $data
     * @return self
     * @since  6.0
     */
    public function setAll(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get an item.
     *
     * @param  string      $key
     * @param  string|null $default
     * @return string|array|null
     */
    public function get(string $key, string|array|null $default = null): string|array|null
    {
        return array_fetch($this->data, $key, $default);
    }

    /**
     * Get many items.
     *
     * @param  array      $keys
     * @param  array|null $default
     * @return array
     */
    public function getAll(array $keys, array|null $default = null): array|null
    {
        return array_fetch($this->data, $keys, $default);
    }

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return self
     * @since  6.0
     */
    public function remove(string $key): self
    {
        array_fetch($this->data, $key, drop: true);

        return $this;
    }

    /**
     * Remove many items.
     *
     * @param  array<string> $keys
     * @return self
     * @since  6.0
     */
    public function removeAll(array $keys): self
    {
        array_fetch($this->data, $keys, drop: true);

        return $this;
    }

    /**
     * @inheritDoc froq\common\interface\Objectable
     */
    public function toObject(): object
    {
        return Util::makeObject($this->data);
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        return Util::buildQueryString($this->data);
    }

    /**
     * Create an instance from given array.
     *
     * @param  array $data
     * @return froq\http\UrlQuery
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Create an instance from given string.
     *
     * @param  string $data
     * @return froq\http\UrlQuery
     */
    public static function fromString(string $data): self
    {
        return new self($data);
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetGet(mixed $key): string
    {
        return $this->get($key);
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetUnset(mixed $key): void
    {
        $this->remove($key);
    }

    /**
     * Map data to uniform as string.
     */
    private static function mapData(array $data): array
    {
        return array_map_recursive(function ($value) {
            if (is_bool($value)) {
                $value = (int) $value;
            }
            return (string) $value;
        }, $data);
    }
}
