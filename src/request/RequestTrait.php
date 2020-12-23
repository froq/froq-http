<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\common\{HeaderTrait, CookieTrait, ParamTrait};

/**
 * Request Trait.
 *
 * @package  froq\http\request
 * @object   froq\http\request\RequestTrait
 * @author   Kerem Güneş
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait RequestTrait
{
    /** @see froq\http\common\HeaderTrait */
    use HeaderTrait;

    /** @see froq\http\common\CookieTrait */
    use CookieTrait;

    /** @see froq\http\common\ParamTrait */
    use ParamTrait {
        ParamTrait::cookie insteadof CookieTrait;
        ParamTrait::hasCookie insteadof CookieTrait;
    }

    /**
     * Is get.
     *
     * @return bool
     * @since  4.3
     */
    public function isGet(): bool
    {
        return $this->method->isGet();
    }

    /**
     * Is post.
     *
     * @return bool
     * @since  4.3
     */
    public function isPost(): bool
    {
        return $this->method->isPost();
    }

    /**
     * Is put.
     *
     * @return bool
     * @since  4.3
     */
    public function isPut(): bool
    {
        return $this->method->isPut();
    }

    /**
     * Is patch.
     *
     * @return bool
     * @since  4.3
     */
    public function isPatch(): bool
    {
        return $this->method->isPatch();
    }

    /**
     * Is delete.
     *
     * @return bool
     * @since  4.3
     */
    public function isDelete(): bool
    {
        return $this->method->isDelete();
    }

    /**
     * Is options.
     *
     * @return bool
     * @since  4.3
     */
    public function isOptions(): bool
    {
        return $this->method->isOptions();
    }

    /**
     * Is head.
     *
     * @return bool
     * @since  4.3
     */
    public function isHead(): bool
    {
        return $this->method->isHead();
    }

    /**
     * Is trace.
     *
     * @return bool
     * @since  4.3
     */
    public function isTrace(): bool
    {
        return $this->method->isTrace();
    }

    /**
     * Is connect.
     *
     * @return bool
     * @since  4.3
     */
    public function isConnect(): bool
    {
        return $this->method->isConnect();
    }

    /**
     * Is copy.
     *
     * @return bool
     * @since  4.3
     */
    public function isCopy(): bool
    {
        return $this->method->isCopy();
    }

    /**
     * Is move.
     *
     * @return bool
     * @since  4.3
     */
    public function isMove(): bool
    {
        return $this->method->isMove();
    }

    /**
     * Is ajax.
     *
     * @return bool
     * @since  4.4
     */
    public function isAjax(): bool
    {
        return $this->method->isAjax();
    }
}
