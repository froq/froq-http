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

use Froq\Util\Traits\GetterTrait;

/**
 * @package    Froq
 * @subpackage Froq\Http\Response
 * @object     Froq\Http\Response\Body
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Body
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use GetterTrait;

    /**
    * Types.
    * @const string
    */
   const CONTENT_TYPE_NONE      = 'none',
         CONTENT_TYPE_HTML      = 'text/html',
         CONTENT_TYPE_PLAIN     = 'text/plain',
         CONTENT_TYPE_XML       = 'application/xml',
         CONTENT_TYPE_JSON      = 'application/json';

    /**
     * Charsets.
     * @const string
     */
    const CONTENT_CHARSET_UTF8  = 'utf-8',
          CONTENT_CHARSET_UTF16 = 'utf-16',
          CONTENT_CHARSET_UTF32 = 'utf-32';

    /**
     * Content.
     * @var string
     */
    private $content;

    /**
     * Content type.
     * @var string
     */
    private $contentType;

    /**
     * Content charset.
     * @var string
     */
    private $contentCharset;

    /**
     * Content length.
     * @var int
     */
    private $contentLength;

    /**
     * Constructor.
     * @param any|null $content
     * @param string   $contentType
     * @param string   $contentCharset
     */
    final public function __construct($content = null,
        string $contentType = self::CONTENT_TYPE_HTML,
        string $contentCharset = self::CONTENT_CHARSET_UTF8)
    {
        $this->setContent($content)
            ->setContentType($contentType)
            ->setContentCharset($contentCharset);
    }

    /**
     * Stringer.
     * @return string
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Set content.
     * @param  any $content
     * @return self
     */
    final public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     * @return any
     */
    final public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content type.
     * @param  string      $contentType
     * @param  string|null $contentCharset For shortcut calls.
     * @return self
     */
    final public function setContentType(string $contentType, string $contentCharset = null): self
    {
        $this->contentType = $contentType;

        // set content charset, "" removes charset but NULL
        if ($contentCharset !== null) {
            $this->setContentCharset($contentCharset);
        }

        return $this;
    }

    /**
     * Get content type.
     * @return string
     */
    final public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Set content charset.
     * @param  string $contentCharset
     * @return self
     */
    final public function setContentCharset(string $contentCharset): self
    {
        $this->contentCharset = $contentCharset;

        return $this;
    }

    /**
     * Get content charset.
     * @return string
     */
    final public function getContentCharset(): string
    {
        return $this->contentCharset;
    }

    /**
     * Set content length.
     * @param  int $contentLength
     * @return self
     */
    final public function setContentLength(int $contentLength): self
    {
        $this->contentLength = $contentLength;

        return $this;
    }

    /**
     * Get content length.
     * @return int|null
     */
    final public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * To string.
     * @return string
     */
    final public function toString(): string
    {
        return (string) $this->content;
    }
}
