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
