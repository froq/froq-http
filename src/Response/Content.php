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

/**
 * @package    Froq
 * @subpackage Froq\Http\Response
 * @object     Froq\Http\Response\Content
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Content
{
    /**
    * Types.
    * @const string
    */
   const TYPE_NONE = 'none',
         TYPE_HTML = 'text/html',
         TYPE_XML  = 'application/xml',
         TYPE_JSON = 'application/json';

    /**
     * Charset.
     * @const string
     */
    const CHARSET_UTF8 = 'utf-8';

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
     * Constructor.
     * @param string|null $type
     * @param string|null $charset
     */
    final public function __construct(string $type = null, string $charset = null)
    {
        if ($type) {
           $this->setType($type);
        }
        if ($charset) {
            $this->setCharset($charset);
        }
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
}
