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

namespace froq\http\response;

use froq\http\response\StatusException;

/**
 * Status.
 *
 * Respresents an HTTP Status Code object with some utility methods.
 *
 * @package froq\http\response
 * @object  froq\http\response\Status
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0, 4.0
 */
final class Status extends StatusCodes
{
    /**
     * Code.
     * @var int
     */
    private int $code;

    /**
     * Text.
     * @var ?string
     */
    private ?string $text;

    /**
     * Constructor.
     * @param int         $code
     * @param string|null $text
     */
    public function __construct(int $code = self::OK, string $text = null)
    {
        $this->setCode($code);
        $this->setText($text ?? self::getTextByCode($code));
    }

    /**
     * Set code.
     * @param  int $code
     * @return void
     * @throws froq\http\response\StatusException
     */
    public function setCode(int $code): void
    {
        if (!self::validate($code)) {
            throw new StatusException('Invalid code '. $code);
        }

        $this->code = $code;
    }

    /**
     * Get code.
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Set text.
     * @param  ?string $text
     * @return void
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    /**
     * Get text.
     * @param  int $code
     * @return ?string
     */
    public function getText(): ?string
    {
        return $this->text;
    }
}
