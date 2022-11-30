<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\common\interface\{Arrayable, Listable, Stringable};
use froq\collection\trait\{FilterTrait, MapTrait, CountTrait, EmptyTrait, GetTrait, ToArrayTrait, ToListTrait};
use froq\util\Util;

/**
 * An array-like class for working with URL-queries in OOP-style.
 *
 * @package froq\http
 * @object  froq\http\UrlQuery
 * @author  Kerem Güneş
 * @since   5.1
 */
class UrlQuery implements Arrayable, Listable, Stringable, \Countable, \ArrayAccess
{
    use FilterTrait, MapTrait, CountTrait, EmptyTrait, GetTrait, ToArrayTrait, ToListTrait;

    /** @var array */
    private array $data = [];

    /**
     * Constructor.
     *
     * @param array|string $data
     */
    public function __construct(array|string $data)
    {
        $this->data = is_array($data) ? $this->mapData($data)
            : http_parse_query_string($data);
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

    /** @magic */
    public function __toString(): string
    {
        return $this->toString();
    }

    /** @magic */
    public function __debugInfo(): array
    {
        return $this->data;
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
            $value = $this->mapData($value);
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
     * @param  string     $key
     * @param  mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return array_get($this->data, $key, $default);
    }

    /**
     * Get many items.
     *
     * @param  array      $keys
     * @param  array|null $defaults
     * @return array
     */
    public function getAll(array $keys, array $defaults = null): array
    {
        return array_get_all($this->data, $keys, $defaults);
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
        array_remove($this->data, $key);

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
        array_remove_all($this->data, $keys);

        return $this;
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        return http_build_query_string($this->data);
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
    private function mapData(array $data): array
    {
        return array_map_recursive(
            function (mixed $value): string {
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                return (string) $value;
            },
            $data
        );
    }
}
