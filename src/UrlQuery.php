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
final class UrlQuery implements Listable, Arrayable, Objectable, Stringable
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
    private array $data;

    /**
     * Constructor.
     *
     * @param array|string $data
     */
    public function __construct(array|string $data)
    {
        is_array($data) || $data = Util::parseQueryString($data);

        $data = array_map_recursive('strval', $data);

        $this->data = $data;
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
     * Get a value by given key.
     *
     * @param  string      $key
     * @param  string|null $default
     * @return string|null
     */
    public function get(string $key, string|null $default = null): string|null
    {
        return array_fetch($this->data, $key, $default);
    }

    /**
     * Get all values by given keys.
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
