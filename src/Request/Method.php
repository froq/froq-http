<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *    <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *    <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Froq\Http\Request;

use Froq\Http\Http;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\Method
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Method
{
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
     * Stringer.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set name.
     * @param  string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = strtoupper($name);

        return $this;
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
        return ($this->name == Http::METHOD_GET);
    }

    /**
     * Is post.
     * @return bool
     */
    public function isPost(): bool
    {
        return ($this->name == Http::METHOD_POST);
    }

    /**
     * Is put.
     * @return bool
     */
    public function isPut(): bool
    {
        return ($this->name == Http::METHOD_PUT);
    }

    /**
     * Is patch.
     * @return bool
     */
    public function isPatch(): bool
    {
        return ($this->name == Http::METHOD_PATCH);
    }

    /**
     * Is delete.
     * @return bool
     */
    public function isDelete(): bool
    {
        return ($this->name == Http::METHOD_DELETE);
    }

    /**
     * Is options.
     * @return bool
     */
    public function isOptions(): bool
    {
        return ($this->name == Http::METHOD_OPTIONS);
    }

    /**
     * Is head.
     * @return bool
     */
    public function isHead(): bool
    {
        return ($this->name == Http::METHOD_HEAD);
    }

    /**
     * Is trace.
     * @return bool
     */
    public function isTrace(): bool
    {
        return ($this->name == Http::METHOD_TRACE);
    }

    /**
     * Is connect.
     * @return bool
     */
    public function isConnect(): bool
    {
        return ($this->name == Http::METHOD_CONNECT);
    }

    /**
     * Is copy.
     * @return bool
     */
    public function isCopy(): bool
    {
        return ($this->name == Http::METHOD_COPY);
    }

    /**
     * Is move.
     * @return bool
     */
    public function isMove(): bool
    {
        return ($this->name == Http::METHOD_MOVE);
    }

    /**
     * Is ajax.
     * @return bool
     */
    public function isAjax(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }

        if (isset($_SERVER['HTTP_X_AJAX'])) {
            return (strtolower($_SERVER['HTTP_X_AJAX']) == 'true' || $_SERVER['HTTP_X_AJAX'] == '1');
        }

        return false;
    }

    /**
     * To string.
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }
}
