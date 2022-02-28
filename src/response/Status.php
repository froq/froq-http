<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response;

use froq\http\response\{Statuses, StatusException};

/**
 * Status.
 *
 * An HTTP Status Code class with some utility methods.
 *
 * @package froq\http\response
 * @object  froq\http\response\Status
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Status extends Statuses
{
    /** @var int */
    private int $code = 0;

    /** @var ?string */
    private ?string $text = null;

    /**
     * Constructor.
     *
     * @param int         $code
     * @param string|null $text
     */
    public function __construct(int $code = 0, string $text = null)
    {
        $code && $this->setCode($code);
        $text && $this->setText($text);
    }

    /**
     * Set code.
     *
     * @param  int $code
     * @return void
     * @throws froq\http\response\StatusException
     */
    public function setCode(int $code): void
    {
        self::validate($code) || throw new StatusException(
            'Invalid code ' . $code
        );

        $this->code = $code;
    }

    /**
     * Get code.
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Set text.
     *
     * @param  string|null $text
     * @return void
     */
    public function setText(string|null $text): void
    {
        $this->text = $text;
    }

    /**
     * Get text.
     *
     * @param  int $code
     * @return string|null
     */
    public function getText(): string|null
    {
        return $this->text;
    }

    /**
     * Code is 200?
     *
     * @return bool
     * @since  6.0
     */
    public function ok(): bool
    {
        return ($this->code == 200);
    }

    /**
     * Code is success code?
     *
     * @return bool
     * @since  6.0
     */
    public function isSucces(): bool
    {
        return ($this->code >= 200 && $this->code <= 299);
    }

    /** @aliasOf isError() */
    public function isFailure(): bool
    {
        return $this->isError();
    }

    /**
     * Code is redirect code?
     *
     * @return bool
     * @since  6.0
     */
    public function isRedirect(): bool
    {
        return ($this->code >= 300 && $this->code <= 399);
    }

    /**
     * Code is error code?
     *
     * @return bool
     * @since  6.0
     */
    public function isError(): bool
    {
        return ($this->isClientError() || $this->isServerError());
    }

    /**
     * Code is client-error code?
     *
     * @return bool
     * @since  6.0
     */
    public function isClientError(): bool
    {
        return ($this->code >= 400 && $this->code <= 499);
    }

    /**
     * Code is server-error code?
     *
     * @return bool
     * @since  6.0
     */
    public function isServerError(): bool
    {
        return ($this->code >= 500 && $this->code <= 599);
    }

    /**
     * Validate given status code.
     *
     * @param  int $code
     * @return bool
     */
    public static function validate(int $code): bool
    {
        // @cancel
        // Since only IANA-defined codes are there, use defined codes only.
        // return array_key_exists($code, parent::all());

        return ($code >= 100 && $code <= 599);
    }

    /**
     * Get code by text.
     *
     * @param  string $text
     * @return int|null
     */
    public static function getCodeByText(string $text): int|null
    {
        return array_find_key(parent::all(), fn($_text) => $_text == $text);
    }

    /**
     * Get text by code.
     *
     * @param  int $code
     * @return string|null
     */
    public static function getTextByCode(int $code): string|null
    {
        return array_find(parent::all(), fn($_, $_code) => $_code == $code);
    }
}
