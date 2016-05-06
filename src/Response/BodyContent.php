<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
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

namespace Froq\Http\Response;

use Froq\Util\Traits\GetterTrait as Getter;

/**
 * @package    Froq
 * @subpackage Froq\Http\Response
 * @object     Froq\Http\Response\BodyContent
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class BodyContent
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use Getter;

    /**
    * Types.
    * @const string
    */
   const TYPE_NONE      = 'none',
         TYPE_HTML      = 'text/html',
         TYPE_PLAIN     = 'text/plain',
         TYPE_XML       = 'application/xml',
         TYPE_JSON      = 'application/json';

    /**
     * Charsets.
     * @const string
     */
    const CHARSET_UTF8  = 'utf-8',
          CHARSET_UTF16 = 'utf-16',
          CHARSET_UTF32 = 'utf-32';

    /**
     * Data.
     * @var string
     */
    private $data;

    /**
     * Type.
     * @var string
     */
    private $type;

    /**
     * Charset.
     * @var string
     */
    private $charset;

    /**
     * Length.
     * @var int
     */
    private $length;

    /**
     * Constructor.
     * @param string|null $data
     * @param string      $type
     * @param string      $charset
     */
    final public function __construct(string $data = null,
        string $type = self::TYPE_HTML, string $charset = self::CHARSET_UTF8)
    {
        $this->setData($data)
             ->setType($type)
             ->setCharset($charset);
    }

    /**
     * Get string data.
     * @return string
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Set data.
     * @param  string|null $data
     * @return self
     */
    final public function setData(string $data = null): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     * @return string|null
     */
    final public function getData()
    {
        return $this->data;
    }

    /**
     * Set type.
     * @param  string $type
     * @return self
     */
    final public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     * @return string|null
     */
    final public function getType()
    {
        return $this->type;
    }

    /**
     * Set charset.
     * @param  string $charset
     * @return self
     */
    final public function setCharset(string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get charset.
     * @param string|null
     */
    final public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set length.
     * @param int $length
     */
    final public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     * @return int|null
     */
    final public function getLength()
    {
        return $this->length;
    }

    /**
     * Get string data.
     * @return string
     */
    final public function toString(): string
    {
        return ((string) $this->data);
    }
}
