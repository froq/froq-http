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

namespace froq\http\common;

/**
 * Content Trait.
 *
 * Represents a trait stack that used by both Request and Response objects, utilizes accessing (to
 * Request & Response) / modifying (of Response only) body's content type & content charset.
 *
 * @package  froq\http\common
 * @object   froq\http\common\ContentTrait
 * @author   Kerem Güneş <k-gun@mail.com>
 * @since    4.0
 * @internal Used in froq\http only.
 */
trait ContentTrait
{
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
