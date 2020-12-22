<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\common\interface\Stringable;

/**
 * Scheme.
 *
 * @package froq\http\request
 * @object  froq\http\request\Scheme
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 */
final class Scheme implements Stringable
{
    /**
     * Names.
     * @const string
     */
    public const HTTP  = 'http',
                 HTTPS = 'https';

    /** @var string */
    private string $name;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Set name.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = strtolower($name);
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Is secure.
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return ($this->name == self::HTTPS);
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        return $this->name;
    }
}
