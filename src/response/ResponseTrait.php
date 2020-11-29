<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response;

use froq\http\common\{HeaderTrait, CookieTrait};

/**
 * Response Trait.
 * @package  froq\http\response
 * @object   froq\http\response\ResponseTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait ResponseTrait
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
     * Set status code.
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
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status->getCode();
    }

    /**
     * Set content type.
     * @param  ?string $type
     * @return self
     */
    public function setContentType(?string $type): self
    {
        $this->body->setAttribute('type', $type);

        return $this;
    }

    /**
     * Get content type.
     * @return ?string
     */
    public function getContentType(): ?string
    {
        return $this->body->getAttribute('type');
    }

    /**
     * Set content charset.
     * @param  ?string $charset
     * @return self
     */
    public function setContentCharset(?string $charset): self
    {
        $this->body->setAttribute('charset', $charset);

        return $this;
    }

    /**
     * Get content charset.
     * @return ?string
     */
    public function getContentCharset(): ?string
    {
        return $this->body->getAttribute('charset');
    }
}
