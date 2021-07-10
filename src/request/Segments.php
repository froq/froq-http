<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\exception\UnsupportedOperationException;
use froq\common\interface\{Arrayable, Listable};
use froq\{Router, mvc\Controller};
use Countable, ArrayAccess;

/**
 * Segments.
 *
 * Represents a read-only segment stack object with some utility methods.
 *
 * @package froq\http\request
 * @object  froq\http\request\Segments
 * @author  Kerem Güneş
 * @since   4.1
 */
final class Segments implements Arrayable, Listable, Countable, ArrayAccess
{
    /**
     * Root.
     * @const string
     */
    public const ROOT = '/';

    /** @var array */
    private array $stack = [];

    /** @var string */
    private string $stackRoot = self::ROOT;

    /**
     * Constructor.
     *
     * @param array|null  $stack
     * @param string|null $stackRoot
     */
    public function __construct(array $stack = null, string $stackRoot = null)
    {
        $stack     && $this->stack     = $stack;
        $stackRoot && $this->stackRoot = $stackRoot;
    }

    /**
     * Get stack property.
     *
     * @return array
     */
    public function stack(): array
    {
        return $this->stack;
    }

    /**
     * Get stack-root property.
     *
     * @return string
     */
    public function stackRoot(): string
    {
        return $this->stackRoot;
    }

    /**
     * Get controller.
     *
     * @param  bool $suffix
     * @return string|null
     */
    public function getController(bool $suffix = false): string|null
    {
        $controller = $this->stack['controller'] ?? null;

        if ($controller && $suffix) {
            $controller .= Controller::SUFFIX;
        }

        return $controller;
    }

    /**
     * Get action.
     *
     * @param  bool $suffix
     * @return string|null
     */
    public function getAction(bool $suffix = false): string|null
    {
        $action = $this->stack['action'] ?? null;

        if ($action && $suffix) {
            $action .= Controller::ACTION_SUFFIX;
        }

        return $action;
    }

    /**
     * Get params.
     *
     * @param  bool $list
     * @return array|null
     */
    public function getParams(bool $list = false): array|null
    {
        return !$list ? $this->stack['params'] ?? null
                      : $this->stack['paramsList'] ?? null;
    }

    /**
     * Get params list.
     *
     * @return array|null
     */
    public function getParamsList(): array|null
    {
        return $this->stack['paramsList'] ?? null;
    }

    /**
     * Get action params.
     *
     * @param  bool $list
     * @return array|null
     */
    public function getActionParams(bool $list = false): array|null
    {
        return !$list ? $this->stack['actionParams'] ?? null
                      : $this->stack['actionParamsList'] ?? null;
    }

    /**
     * Get action params list.
     *
     * @return array|null.
     */
    public function getActionParamsList(): array|null
    {
        return $this->stack['actionParamsList'] ?? null;
    }

    /**
     * Get a segment param.
     *
     * @param  int|string $key
     * @param  any|null   $default
     * @return any|null
     */
    public function get(int|string $key, $default = null)
    {
        return is_int($key) ? $this->stack['paramsList'][$key - 1] ?? $default
                            : $this->stack['params'][$key] ?? $default;
    }

    /**
     * Get a segment param by given name.
     *
     * @param  string   $name
     * @param  any|null $default
     * @return any|null
     */
    public function getParam(string $name, $default = null)
    {
        return $this->stack['params'][$name] ?? $default;
    }

    /**
     * Get an action param.
     *
     * @param  int|string $key
     * @param  any|null   $default
     * @return any|null
     */
    public function getActionParam(int|string $key, $default = null)
    {
        return is_int($key) ? $this->stack['actionParamsList'][$key - 1] ?? $default
                            : $this->stack['actionParams'][$key] ?? $default;
    }

    /**
     * From array.
     *
     * @param  array $array
     * @return froq\http\request\Segments
     */
    public static function fromArray(array $array): Segments
    {
        $array = array_values($array);

        [$controller, $action] = [
            Router::prepareControllerName($array[0] ?? Controller::DEFAULT_SHORT, false),
            Router::prepareActionName($array[1] ?? Controller::ACTION_DEFAULT, false)
        ];

        $stack = [
            'controller'   => $controller,
            'action'       => $action,
            'params'       => [], 'paramsList' => [],
            'actionParams' => [], 'actionParamsList' => [],
        ];

        $paramsList = $array;
        foreach (array_chunk($array, 2) as $chunk) {
            $stack['params'][$chunk[0]] = $chunk[1] ?? '';
        }

        $actionParamsList = array_slice($array, 2);
        foreach (array_chunk($actionParamsList, 2) as $chunk) {
            $stack['actionParams'][$chunk[0]] = $chunk[1] ?? '';
        }

        // @cancel
        // Setting indexes from 1, not 0.
        // array_unshift($paramsList, null);
        // array_unshift($actionParamsList, null);

        $stack['paramsList']       = array_filter($paramsList, 'strlen');
        $stack['actionParamsList'] = array_filter($actionParamsList, 'strlen');

        return new Segments($stack);
    }

    /**
     * Check whether param list empty.
     *
     * @return bool
     * @since  4.2, 4.9 Renamed from empty().
     */
    public function isEmpty(): bool
    {
        return empty($this->getParamsList());
    }

    /**
     * @inheritDoc froq\common\interface\Listable
     * @param      int $offset
     * @since      4.2
     */
    public function toList(int $offset = 0): array
    {
        return array_slice((array) $this->getParamsList(), $offset);
    }

    /**
     * @inheritDoc froq\common\interface\Arrayable
     */
    public function toArray(): array
    {
        return $this->getStack();
    }

    /**
     * @inheritDoc Countable
     * @since 4.9
     */
    public function count(): int
    {
        return count((array) $this->getParamsList());
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetExists($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exception\UnsupportedOperationException
     */
    public function offsetSet($key, $value)
    {
        throw new UnsupportedOperationException('No set() allowed for object' . self::class);
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exception\UnsupportedOperationException
     */
    public function offsetUnset($key)
    {
        throw new UnsupportedOperationException('No unset() allowed for object' . self::class);
    }
}
