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

namespace froq\http\request;

use froq\common\interfaces\Arrayable;
use froq\common\exceptions\{InvalidKeyException, UnsupportedOperationException};
use froq\{Router, mvc\Controller};
use ArrayAccess;

/**
 * Segments.
 *
 * Respresents a segment stack object with some utility methods.
 *
 * @package froq\http\request
 * @object  froq\http\request\Segments
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.1
 */
final class Segments implements Arrayable, ArrayAccess
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
        $stackRoot && $this->stackroot = $stackRoot;
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
     * @return ?string
     */
    public function getController(): ?string
    {
        return $this->stack['controller'] ?? null;
    }

    /**
     * Get action.
     * @return ?string
     */
    public function getAction(): ?string
    {
        return $this->stack['action'] ?? null;
    }

    /**
     * Get action params.
     * @return ?array
     */
    public function getActionParams(): ?array
    {
        return $this->stack['actionParams'] ?? null;
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
     * Get params.
     * @return ?array
     */
    public function getParams(): ?array
    {
        return $this->stack['params'] ?? null;
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
     * Get.
     * @param  int|string $key
     * @param  any|null   $valueDefault
     * @return any|null
     * @throws froq\common\exceptions\InvalidKeyException
     */
    public function get($key, $valueDefault = null)
    {
        if (is_int($key)) {
            return $this->stack['paramsList'][$key] ?? $valueDefault;
        }
        if (is_string($key)) {
            return $this->stack['params'][$key] ?? $valueDefault;
        }

        throw new InvalidKeyException('Key type must be int|string, "%s" given', [gettype($key)]);
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
            return $this->stack['actionParamsList'][$key] ?? $valueDefault;
        }
        if (is_string($key)) {
            return $this->stack['actionParams'][$key] ?? $valueDefault;
        }

        throw new InvalidKeyException('Key type must be int|string, "%s" given', [gettype($key)]);
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
            Router::prepareControllerName($array[0] ?? Controller::DEFAULT_NAME, false),
            Router::prepareControllerActionName($array[1] ?? Controller::INDEX_ACTION, false)
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

        // Setting indexes from 1, not 0.
        array_unshift($paramsList, null);
        array_unshift($actionParamsList, null);

        $stack['paramsList'] = array_filter($paramsList, 'strlen');
        $stack['actionParamsList'] = array_filter($actionParamsList, 'strlen');

        return new Segments($stack);
    }

    /**
     * @inheritDoc froq\common\interfaces\Arrayable
     */
    public function toArray(): array
    {
        return $this->getStack();
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public final function offsetExists($name)
    {
        return isset($this->stack[$name]);
    }

    /**
     * @inheritDoc ArrayAccess
     */
    public final function offsetGet($name)
    {
        return $this->stack[$name] ?? null;
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exceptions\UnsupportedOperationException
     */
    public final function offsetSet($name, $value)
    {
        throw new UnsupportedOperationException('No set() allowed for "%s"', [self::class]);
    }

    /**
     * @inheritDoc ArrayAccess
     * @throws     froq\common\exceptions\UnsupportedOperationException
     */
    public final function offsetUnset($name)
    {
        throw new UnsupportedOperationException('No unset() allowed for "%s"', [self::class]);
    }
}
