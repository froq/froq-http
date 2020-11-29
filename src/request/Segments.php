<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\exceptions\{InvalidKeyException, UnsupportedOperationException};
use froq\common\interfaces\Arrayable;
use froq\{Router, mvc\Controller};
use Countable, ArrayAccess;

/**
 * Segments.
 *
 * Respresents a read-only segment stack object with some utility methods.
 *
 * @package froq\http\request
 * @object  froq\http\request\Segments
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.1
 */
final class Segments implements Arrayable, Countable, ArrayAccess
{
    /**
     * Root.
     * @const string
     */
    public const ROOT = '/';

    /**
     * Stack.
     * @var array
     */
    private array $stack = [];

    /**
     * Stack root.
     * @var string
     */
    private string $stackRoot = '/';

    /**
     * Constructor.
     * @param array|null  $stack
     * @param string|null $stackRoot
     */
    public function __construct(array $stack = null, string $stackRoot = null)
    {
        $stack && $this->stack = $stack;
        $stackRoot && $this->stackRoot = $stackRoot;
    }

    /**
     * Get stack.
     * @return array
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * Get stack root.
     * @return string
     */
    public function getStackRoot(): string
    {
        return $this->stackRoot;
    }

    /**
     * Get controller.
     * @param  bool $suffix
     * @return ?string
     */
    public function getController(bool $suffix = false): ?string
    {
        $controller = $this->stack['controller'] ?? null;

        if ($controller && $suffix) {
            $controller .= Controller::SUFFIX;
        }

        return $controller;
    }

    /**
     * Get action.
     * @param  bool $suffix
     * @return ?string
     */
    public function getAction(bool $suffix = false): ?string
    {
        $action = $this->stack['action'] ?? null;

        if ($action && $suffix) {
            $action .= Controller::ACTION_SUFFIX;
        }

        return $action;
    }

    /**
     * Get params.
     * @param  bool $list
     * @return ?array
     */
    public function getParams(bool $list = false): ?array
    {
        return !$list ? $this->stack['params'] ?? null
                      : $this->stack['paramsList'] ?? null;
    }

    /**
     * Get params list.
     * @return ?array
     */
    public function getParamsList(): ?array
    {
        return $this->stack['paramsList'] ?? null;
    }

    /**
     * Get action params.
     * @param  bool $list
     * @return ?array
     */
    public function getActionParams(bool $list = false): ?array
    {
        return !$list ? $this->stack['actionParams'] ?? null
                      : $this->stack['actionParamsList'] ?? null;
    }

    /**
     * Get action params list.
     * @return ?array.
     */
    public function getActionParamsList(): ?array
    {
        return $this->stack['actionParamsList'] ?? null;
    }

    /**
     * Get.
     * @param  int|string $key
     * @param  any|null   $valueDefault
     * @return any|null
     * @throws froq\common\exceptions\InvalidKeyException
     */
    public function get($key, $valueDefault = null)
    {
        if (is_int($key)) {
            return $this->stack['paramsList'][$key - 1] ?? $valueDefault;
        }
        if (is_string($key)) {
            return $this->stack['params'][$key] ?? $valueDefault;
        }

        throw new InvalidKeyException("Key type must be int|string, '%s' given", gettype($key));
    }

    /**
     * Get.
     * @param  int|string $key
     * @param  any|null   $valueDefault
     * @return any|null
     * @throws froq\common\exceptions\InvalidKeyException
     */
    public function getActionParam($key, $valueDefault = null)
    {
        if (is_int($key)) {
            return $this->stack['actionParamsList'][$key - 1] ?? $valueDefault;
        }
        if (is_string($key)) {
            return $this->stack['actionParams'][$key] ?? $valueDefault;
        }

        throw new InvalidKeyException("Key type must be int|string, '%s' given", gettype($key));
    }

    /**
     * From array.
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
            'params'       => [], 'paramsList' => [],
            'controller'   => $controller,
            'action'       => $action,
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

        $stack['paramsList'] = array_filter($paramsList, 'strlen');
        $stack['actionParamsList'] = array_filter($actionParamsList, 'strlen');

        return new Segments($stack);
    }

    /**
     * Empty.
     * @return bool
     * @since  4.2, 4.9 Renamed from empty().
     */
    public function isEmpty(): bool
    {
        return empty($this->getParamsList());
    }

    /**
     * To list.
     * @param  int $offset
     * @return array
     * @since  4.2
     */
    public function toList(int $offset = 0): array
    {
        return array_slice((array) $this->getParamsList(), $offset);
    }

    /**
     * @inheritDoc froq\common\interfaces\Arrayable
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
    public function offsetExists($name)
    {
        return isset($this->stack[$name]);
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public function offsetGet($name)
    {
        return $this->stack[$name] ?? null;
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exceptions\UnsupportedOperationException
     */
    public function offsetSet($name, $value)
    {
        throw new UnsupportedOperationException("No set() allowed for '%s' object", self::class);
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exceptions\UnsupportedOperationException
     */
    public function offsetUnset($name)
    {
        throw new UnsupportedOperationException("No unset() allowed for '%s' object", self::class);
    }
}
