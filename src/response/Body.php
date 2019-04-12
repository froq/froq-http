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

namespace froq\http\response;

use froq\http\HttpException;

/**
 * Body.
 * @package froq\http\response
 * @object  froq\http\response\Body
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Body
{
    /**
    * Content types.
    * @const string
    */
    public const CONTENT_TYPE_NA                 = 'n/a',
                 CONTENT_TYPE_NONE               = 'none',
                 CONTENT_TYPE_HTML               = 'text/html',
                 CONTENT_TYPE_PLAIN              = 'text/plain',
                 CONTENT_TYPE_XML                = 'application/xml',
                 CONTENT_TYPE_XML_TEXT           = 'text/xml',
                 CONTENT_TYPE_JSON               = 'application/json',
                 CONTENT_TYPE_JSON_TEXT          = 'text/json';

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
     * @param any|null    $content
     * @param string|null $contentType
     * @param string|null $contentCharset
     */
    public function __construct($content = null, string $contentType = null, string $contentCharset = null)
    {
        $this->content = $content;
        $this->contentType = $contentType ?? self::CONTENT_TYPE_HTML; // @default
        $this->contentCharset = $contentCharset ?? self::CONTENT_CHARSET_UTF_8; // @default

        // auto-set for content length
        if ($this->isString()) {
            $this->setContentLength(strlen($content));
        } elseif ($this->isImage()) {
            ob_start();
            switch ($contentType) {
                case 'image/png':
                    imagepng($content) && $this->setContentLength(ob_get_length());
                    break;
                case 'image/gif':
                    imagegif($content) && $this->setContentLength(ob_get_length());
                    break;
                case 'image/jpeg':
                    imagejpeg($content) && $this->setContentLength(ob_get_length());
                    break;
                default:
                    throw new HttpException("Unimplemented content type '{$contentType}'");
            }
            ob_end_clean();
        }
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
     * @param  any|null $content
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     * @return any|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content type.
     * @param  string      $contentType
     * @param  string|null $contentCharset
     * @return self
     */
    public function setContentType(string $contentType, string $contentCharset = null): self
    {
        $this->contentType = $contentType;

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
     * @return int
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    // @wait
    // public function isFile(): bool {}

    /**
     * Is image.
     * @return bool
     */
    public function isImage(): bool
    {
        return strpos($this->contentType, 'image/') === 0;
    }

    /**
     * Is string.
     * @return bool
     */
    public function isString(): bool
    {
        return is_string($this->content) && !$this->isImage();
    }

    /**
     * To string.
     * @return string
     */
    public function toString(): string
    {
        $content = $this->content;
        $contentType = $this->contentType;

        if ($this->isString()) {
            return $content;
        } elseif ($this->isImage()) {
            switch ($contentType) {
                case 'image/png':
                    imagepng($content) && imagedestroy($content);
                    break;
                case 'image/gif':
                    imagegif($content) && imagedestroy($content);
                    break;
                case 'image/jpeg':
                    imagejpeg($content) && imagedestroy($content);
                    break;
                default:
                    throw new HttpException("Unimplemented content type '{$contentType}'");
            }
            return '';
        }
    }
}
