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
 * @subpackage Froq\Http
 * @object     Froq\Http\Response\Body
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Body
{
    /**
    * Content ypes.
    * @const string
    */
    public const CONTENT_TYPE_NONE               = 'none',
                 CONTENT_TYPE_HTML               = 'text/html',
                 CONTENT_TYPE_PLAIN              = 'text/plain',
                 CONTENT_TYPE_TEXT_XML           = 'text/xml',
                 CONTENT_TYPE_APPLICATION_XML    = 'application/xml',
                 CONTENT_TYPE_TEXT_JSON          = 'text/json',
                 CONTENT_TYPE_APPLICATION_JSON   = 'application/json';

    /**
     * Content charsets.
     * @const string
     */
    public const CONTENT_CHARSET_UTF_8           = 'utf-8',
                 CONTENT_CHARSET_ISO_8859_1      = 'iso-8859-1';

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
    public function __construct($content = null, string $contentType = 'text/html',
        string $contentCharset = 'utf-8')
    {
        $this->setContent($content)
            ->setContentType($contentType)
            ->setContentCharset($contentCharset);
    }

    /**
     * String magic.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set content.
     * @param  any $content
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     * @return any
     */
    public function getContent()
    {
        return $this->content ?? '';
    }

    /**
     * Set content type.
     * @param  string      $contentType
     * @param  string|null $contentCharset For shortcut calls.
     * @return self
     */
    public function setContentType(string $contentType, string $contentCharset = null): self
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
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Set content charset.
     * @param  string $contentCharset
     * @return self
     */
    public function setContentCharset(string $contentCharset): self
    {
        $this->contentCharset = $contentCharset;

        return $this;
    }

    /**
     * Get content charset.
     * @return string
     */
    public function getContentCharset(): string
    {
        return $this->contentCharset;
    }

    /**
     * Set content length.
     * @param  int $contentLength
     * @return self
     */
    public function setContentLength(int $contentLength): self
    {
        $this->contentLength = $contentLength;

        return $this;
    }

    /**
     * Get content length.
     * @return ?int
     */
    public function getContentLength(): ?int
    {
        return $this->contentLength;
    }

    /**
     * To string.
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->content;
    }
}
