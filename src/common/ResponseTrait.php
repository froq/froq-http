<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\common;

use froq\http\common\{HeaderTrait, CookieTrait};

/**
 * Response Trait.
 *
 * @package  froq\http\common
 * @object   froq\http\common\ResponseTrait
 * @author   Kerem Güneş
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait ResponseTrait
{
    /** @see froq\http\common\HeaderTrait */
    use HeaderTrait;

    /** @see froq\http\common\CookieTrait */
    use CookieTrait;

    /**
     * Set status code.
     *
     * @param  int $code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->status->setCode($code);

        return $this;
    }

    /**
     * Get status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status->getCode();
    }

    /**
     * Set content type.
     *
     * @param  string $type
     * @return self
     */
    public function setContentType(string $type): self
    {
        $this->body->setAttribute('type', $type);

        return $this;
    }

    /**
     * Get content type.
     *
     * @return string|null
     */
    public function getContentType(): string|null
    {
        return $this->body->getAttribute('type');
    }

    /**
     * Set content charset.
     *
     * @param  string $charset
     * @return self
     */
    public function setContentCharset(string $charset): self
    {
        $this->body->setAttribute('charset', $charset);

        return $this;
    }

    /**
     * Get content charset.
     *
     * @return string|null
     */
    public function getContentCharset(): string|null
    {
        return $this->body->getAttribute('charset');
    }
}
