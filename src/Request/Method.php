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

/**
 * @package    Froq
 * @subpackage Froq\Http\Request
 * @object     Froq\Http\Request\Method
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Method
{

    /**
     * Methods.
     * @conts string
     */
    const NAME_GET     = 'GET',
          NAME_POST    = 'POST',
          NAME_PUT     = 'PUT',
          NAME_PATCH   = 'PATCH',
          NAME_DELETE  = 'DELETE',
          NAME_OPTIONS = 'OPTIONS',
          NAME_HEAD    = 'HEAD',
          NAME_TRACE   = 'TRACE',
          NAME_CONNECT = 'CONNECT',
          // non-standard
          NAME_COPY    = 'COPY',
          NAME_MOVE    = 'MOVE';

    /**
     * Name.
     * @var string
     */
    private $name = '';

    /**
     * Constructor.
     * @param string $name
     */
    final public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Set name.
     * @param string $name
     */
    final public function setName(string $name): self
    {
        $this->name = strtoupper($name);

        return $this;
    }

    /**
     * Get name.
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * Detect GET method.
     * @return bool
     */
    final public function isGet(): bool
    {
        return ($this->name == self::NAME_GET);
    }

    /**
     * Detect POST method.
     * @return bool
     */
    final public function isPost(): bool
    {
        return ($this->name == self::NAME_POST);
    }

    /**
     * Detect PUT method.
     * @return bool
     */
    final public function isPut(): bool
    {
        return ($this->name == self::NAME_PUT);
    }

    /**
     * Detect PATCH method.
     * @return bool
     */
    final public function isPatch(): bool
    {
        return ($this->name == self::NAME_PATCH);
    }

    /**
     * Detect DELETE method.
     * @return bool
     */
    final public function isDelete(): bool
    {
        return ($this->name == self::NAME_DELETE);
    }

    /**
     * Detect OPTIONS method.
     * @return bool
     */
    final public function isOptions(): bool
    {
        return ($this->name == self::NAME_OPTIONS);
    }

    /**
     * Detect HEAD method.
     * @return bool
     */
    final public function isHead(): bool
    {
        return ($this->name == self::NAME_HEAD);
    }

    /**
     * Detect TRACE method.
     * @return bool
     */
    final public function isTrace(): bool
    {
        return ($this->name == self::NAME_TRACE);
    }

    /**
     * Detect CONNECT method.
     * @return bool
     */
    final public function isConnect(): bool
    {
        return ($this->name == self::NAME_CONNECT);
    }

    /**
     * Detect COPY method.
     * @return bool
     */
    final public function isCopy(): bool
    {
        return ($this->name == self::NAME_COPY);
    }

    /**
     * Detect MOVE method.
     * @return bool
     */
    final public function isMove(): bool
    {
        return ($this->name == self::NAME_MOVE);
    }

    /**
     * Detect AJAX requests.
     * @return bool
     */
    final public function isAjax(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        if (isset($_SERVER['HTTP_X_AJAX'])) {
            return (strtolower($_SERVER['HTTP_X_AJAX']) == 'true' || $_SERVER['HTTP_X_AJAX'] == '1');
        }
        return false;
    }
}
