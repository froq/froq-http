<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response;

use froq\http\response\{StatusCodes, StatusException};

/**
 * Status.
 *
 * Respresents an HTTP Status Code object with some utility methods.
 *
 * @package froq\http\response
 * @object  froq\http\response\Status
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
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
            throw new StatusException('Invalid code ' . $code);
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
