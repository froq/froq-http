<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\interface\{Arrayable, Listable};
use froq\collection\trait\GetTrait;
use froq\common\exception\UnsupportedOperationException;

/**
 * Segments.
 *
 * An internal read-only class that holds URI segment data with some utility methods.
 *
 * @package froq\http\request
 * @object  froq\http\request\Segments
 * @author  Kerem Güneş
 * @since   4.1
 * @internal
 */
final class Segments implements Arrayable, Listable, \Countable, \ArrayAccess
{
    /** @see froq\collection\trait\GetTrait */
    use GetTrait;

    /** @const string */
    public const ROOT = '/';

    /** @var array */
    private array $data = [];

    /** @var string */
    private string $root = self::ROOT;

    /**
     * Constructor.
     *
     * @param array|null  $data
     * @param string|null $root
     */
    public function __construct(array $data = null, string $root = null)
    {
        $data && $this->data = $data;
        $root && $this->root = $root;
    }

    /** @magic */
    public function __set(string $key, string|null $value): void
    {
        $this->offsetSet($key, $value);
    }

    /** @magic */
    public function __get(string $key): string|null
    {
        return $this->offsetGet($key);
    }

    /**
     * Get root.
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get params.
     *
     * @return array|null
     */
    public function params(): array|null
    {
        return $this->data['params'] ?? null;
    }

    /**
     * Get params list.
     *
     * @return array|null
     */
    public function paramsList(): array|null
    {
        return $this->data['paramsList'] ?? null;
    }

    /**
     * Get a segment param.
     *
     * @param  int|string  $key
     * @param  string|null $default
     * @return string|null
     */
    public function get(int|string $key, string $default = null): string|null
    {
        return is_int($key) ? $this->data['paramsList'][$key] ?? $default
                            : $this->data['params'][$key]     ?? $default;
    }

    /**
     * Get a segment param.
     *
     * @param  string      $name
     * @param  string|null $default
     * @return string|null
     */
    public function getParam(string $name, string $default = null): string|null
    {
        return $this->data['params'][$name] ?? $default;
    }

    /**
     * Get many segment params.
     *
     * @param  array<string>|null $names
     * @param  array<string>|null $defaults
     * @return array<string>|null
     */
    public function getParams(array $names = null, array $defaults = null): array|null
    {
        if ($names === null) {
            return $this->data['params'] ?? null;
        }

        $values = [];
        foreach ($names as $i => $name) {
            $values[] = $this->data['params'][$name] ?? $defaults[$i] ?? null;
        }

        return $values ?: $defaults;
    }

    /**
     * From array.
     *
     * @param  array<string> $array
     * @return froq\http\request\Segments
     */
    public static function fromArray(array $array): Segments
    {
        $data = ['params' => [], 'paramsList' => []];

        // Chunk as key/value pairs.
        foreach (array_chunk($array, 2) as $dat) {
            $data['params'][$dat[0]] = $dat[1] ?? null;
        }

        // Index from 1, not 0.
        foreach ($array as $i => $dat) {
            $data['paramsList'][$i + 1] = $dat;
        }


        return new Segments($data);
    }

    /**
     * Check whether param list empty.
     *
     * @return bool
     * @since  4.2, 4.9
     */
    public function isEmpty(): bool
    {
        return empty($this->data['paramsList']);
    }

    /**
     * @inheritDoc froq\common\interface\Listable
     */
    public function toList(): array
    {
        return slice($this->data['paramsList'] ?? [], 0);
    }

    /**
     * @inheritDoc froq\common\interface\Arrayable
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc Countable
     * @since 4.9
     */
    public function count(): int
    {
        return count($this->data['paramsList'] ?? []);
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
     * @throws froq\common\exception\UnsupportedOperationException
     */
    public function offsetSet(mixed $key, mixed $value): never
    {
        throw new UnsupportedOperationException('Cannot modify read-only object ' . self::class);
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws froq\common\exception\UnsupportedOperationException
     */
    public function offsetUnset(mixed $key): never
    {
        throw new UnsupportedOperationException('Cannot modify read-only object ' . self::class);
    }
}
