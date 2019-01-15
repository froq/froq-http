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

namespace Froq\Http\Request;

use Froq\Http\Http;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\Method
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Method
{
    /**
     * Name.
     * @var string
     */
    private $name;

    /**
     * Constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * String magic.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set name.
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = strtoupper($name);
    }

    /**
     * Get name.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Is get.
     * @return bool
     */
    public function isGet(): bool
    {
        return ($this->name == Http::METHOD_GET);
    }

    /**
     * Is post.
     * @return bool
     */
    public function isPost(): bool
    {
        return ($this->name == Http::METHOD_POST);
    }

    /**
     * Is put.
     * @return bool
     */
    public function isPut(): bool
    {
        return ($this->name == Http::METHOD_PUT);
    }

    /**
     * Is patch.
     * @return bool
     */
    public function isPatch(): bool
    {
        return ($this->name == Http::METHOD_PATCH);
    }

    /**
     * Is delete.
     * @return bool
     */
    public function isDelete(): bool
    {
        return ($this->name == Http::METHOD_DELETE);
    }

    /**
     * Is options.
     * @return bool
     */
    public function isOptions(): bool
    {
        return ($this->name == Http::METHOD_OPTIONS);
    }

    /**
     * Is head.
     * @return bool
     */
    public function isHead(): bool
    {
        return ($this->name == Http::METHOD_HEAD);
    }

    /**
     * Is trace.
     * @return bool
     */
    public function isTrace(): bool
    {
        return ($this->name == Http::METHOD_TRACE);
    }

    /**
     * Is connect.
     * @return bool
     */
    public function isConnect(): bool
    {
        return ($this->name == Http::METHOD_CONNECT);
    }

    /**
     * Is copy.
     * @return bool
     */
    public function isCopy(): bool
    {
        return ($this->name == Http::METHOD_COPY);
    }

    /**
     * Is move.
     * @return bool
     */
    public function isMove(): bool
    {
        return ($this->name == Http::METHOD_MOVE);
    }

    /**
     * Is ajax.
     * @return bool
     */
    public function isAjax(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }

        if (isset($_SERVER['HTTP_X_AJAX'])) {
            return (strtolower($_SERVER['HTTP_X_AJAX']) == 'true' || $_SERVER['HTTP_X_AJAX'] == '1');
        }

        return false;
    }

    /**
     * To string.
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }
}
