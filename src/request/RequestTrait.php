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

use froq\http\common\{HeaderTrait, CookieTrait, ParamTrait};

/**
 * Request Trait.
 * @package  froq\http\request
 * @object   froq\http\request\RequestTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait RequestTrait
{
    /**
     * Header trait.
     * @see froq\http\common\HeaderTrait
     */
    use HeaderTrait;

    /**
     * Cookie trait.
     * @see froq\http\common\CookieTrait
     */
    use CookieTrait;

    /**
     * Param trait.
     * @see froq\http\common\ParamTrait
     */
    use ParamTrait {
        ParamTrait::cookie insteadof CookieTrait;
        ParamTrait::hasCookie insteadof CookieTrait;
    }

    /**
     * Is get.
     * @return bool
     * @since  4.3
     */
    public function isGet(): bool
    {
        return $this->method->isGet();
    }

    /**
     * Is post.
     * @return bool
     * @since  4.3
     */
    public function isPost(): bool
    {
        return $this->method->isPost();
    }

    /**
     * Is put.
     * @return bool
     * @since  4.3
     */
    public function isPut(): bool
    {
        return $this->method->isPut();
    }

    /**
     * Is patch.
     * @return bool
     * @since  4.3
     */
    public function isPatch(): bool
    {
        return $this->method->isPatch();
    }

    /**
     * Is delete.
     * @return bool
     * @since  4.3
     */
    public function isDelete(): bool
    {
        return $this->method->isDelete();
    }

    /**
     * Is options.
     * @return bool
     * @since  4.3
     */
    public function isOptions(): bool
    {
        return $this->method->isOptions();
    }

    /**
     * Is head.
     * @return bool
     * @since  4.3
     */
    public function isHead(): bool
    {
        return $this->method->isHead();
    }

    /**
     * Is trace.
     * @return bool
     * @since  4.3
     */
    public function isTrace(): bool
    {
        return $this->method->isTrace();
    }

    /**
     * Is connect.
     * @return bool
     * @since  4.3
     */
    public function isConnect(): bool
    {
        return $this->method->isConnect();
    }

    /**
     * Is copy.
     * @return bool
     * @since  4.3
     */
    public function isCopy(): bool
    {
        return $this->method->isCopy();
    }

    /**
     * Is move.
     * @return bool
     * @since  4.3
     */
    public function isMove(): bool
    {
        return $this->method->isMove();
    }

    /**
     * Is ajax.
     * @return bool
     * @since  4.4
     */
    public function isAjax(): bool
    {
        return $this->method->isAjax();
    }
}
