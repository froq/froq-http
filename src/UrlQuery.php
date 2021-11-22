<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\common\interface\{Arrayable, Objectable, Stringable};
use froq\common\trait\{DataCountTrait, DataEmptyTrait, DataListTrait};
use froq\collection\trait\{EachTrait, FilterTrait, MapTrait};
use froq\util\{Util, Arrays};

/**
 * Url Query.
 *
 * Represents a URL-query class to be available to work in OOP style with URL-queries.
 *
 * @package froq\http
 * @object  froq\http\UrlQuery
 * @author  Kerem Güneş
 * @since   5.1
 */
final class UrlQuery implements Arrayable, Objectable, Stringable
{
    /**
     * @see froq\common\trait\DataCountTrait
     * @see froq\common\trait\DataEmptyTrait
     * @see froq\common\trait\DataListTrait
     */
    use DataCountTrait, DataEmptyTrait, DataListTrait;

    /**
     * @see froq\collection\trait\EachTrait
     * @see froq\collection\trait\FilterTrait
     * @see froq\collection\trait\MapTrait
     */
    use EachTrait, FilterTrait, MapTrait;

    /** @var array */
    private array $data;

    /**
     * Constructor.
     *
     * @param array|string $data
     */
    public function __construct(array|string $data)
    {
        is_array($data) || $data = Util::parseQueryString($data);

        $data = array_map_recursive($data, 'strval');

        $this->data = $data;
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
     * Check whether a key is set & not empty '' / null (usable for dotted notations) and
     * set ref'ed value with fetched value.
     *
     * @param  string    $key
     * @param  any|null &$value
     * @return bool
     */
    public function hasValue(string $key, &$value = null): bool
    {
        $value = $this->get($key);

        return ($value !== null && $value !== '');
    }

    /**
     * Get a value by given key.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return any|null
     */
    public function get(string $key, $default = null)
    {
        return array_fetch($this->data, $key, $default);
    }

    /**
     * Get all values by given keys.
     *
     * @param  array    $keys
     * @param  any|null $default
     * @return array
     */
    public function getAll(array $keys, $default = null): array
    {
        return array_fetch($this->data, $keys, $default);
    }

    /**
     * Get a value as int by given key.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return int
     */
    public function getInt(string $key, $default = null): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Get a value as float by given key.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return float
     */
    public function getFloat(string $key, $default = null): float
    {
        return (float) $this->get($key, $default);
    }

    /**
     * Get a value as string by given key.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return string
     */
    public function getString(string $key, $default = null): string
    {
        return (string) $this->get($key, $default);
    }

    /**
     * Get a value as bool by given key.
     *
     * @param  string   $key
     * @param  any|null $default
     * @return bool
     */
    public function getBool(string $key, $default = null): bool
    {
        return (bool) $this->get($key, $default);
    }

    /**
     * @inheritDoc froq\common\interface\Arrayable
     */
    public function toArray(): array
    {
        return $this->data;
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
}
