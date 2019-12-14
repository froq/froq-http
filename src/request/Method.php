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

use froq\inters\Stringable;

/**
 * Method.
 * @package froq\http\request
 * @object  froq\http\request\Method
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0, 4.0
 */
final class Method implements Stringable
{
    /**
     * Names.
     * @const string
     */
    public const GET     = 'GET',     POST    = 'POST',
                 PUT     = 'PUT',     PATCH   = 'PATCH',
                 DELETE  = 'DELETE',  PURGE   = 'PURGE',
                 OPTIONS = 'OPTIONS', HEAD    = 'HEAD',
                 TRACE   = 'TRACE',   CONNECT = 'CONNECT',
                 COPY    = 'COPY',    MOVE    = 'MOVE',
                 LINK    = 'LINK',    UNLINK  = 'UNLINK';

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
        return $this->name == self::GET;
    }

    /**
     * Is post.
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->name == self::POST;
    }

    /**
     * Is put.
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->name == self::PUT;
    }

    /**
     * Is patch.
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->name == self::PATCH;
    }

    /**
     * Is delete.
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->name == self::DELETE;
    }

    /**
     * Is options.
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->name == self::OPTIONS;
    }

    /**
     * Is head.
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->name == self::HEAD;
    }

    /**
     * Is trace.
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->name == self::TRACE;
    }

    /**
     * Is connect.
     * @return bool
     */
    public function isConnect(): bool
    {
        return $this->name == self::CONNECT;
    }

    /**
     * Is copy.
     * @return bool
     */
    public function isCopy(): bool
    {
        return $this->name == self::COPY;
    }

    /**
     * Is move.
     * @return bool
     */
    public function isMove(): bool
    {
        return $this->name == self::MOVE;
    }

    /**
     * Is ajax.
     * @return bool
     */
    public function isAjax(): bool
    {
        return (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_X_AJAX'])
                && (strtolower($_SERVER['HTTP_X_AJAX']) === 'true' || $_SERVER['HTTP_X_AJAX'] === '1'))
        );
    }

    /**
     * @inheritDoc froq\inters\Stringable
     */
    public function toString(): string
    {
        return $this->name;
    }
}
