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

namespace froq\http\message;

use froq\common\traits\AttributeTrait;

/**
 * Body.
 * @package froq\http\message
 * @object  froq\http\message\Body
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Body
{
    /**
     * Attribute trait.
     * @see froq\common\traits\AttributeTrait
     * @since 4.0
     */
    use AttributeTrait;

    /**
    * Content types.
    * @const string
    */
    public const CONTENT_TYPE_NA                       = 'n/a',
                 // Texts.
                 CONTENT_TYPE_TEXT_HTML                = 'text/html',
                 CONTENT_TYPE_TEXT_PLAIN               = 'text/plain',
                 CONTENT_TYPE_TEXT_XML                 = 'text/xml',
                 CONTENT_TYPE_TEXT_JSON                = 'text/json',
                 CONTENT_TYPE_APPLICATION_XML          = 'application/xml',
                 CONTENT_TYPE_APPLICATION_JSON         = 'application/json',
                 // Images.
                 CONTENT_TYPE_IMAGE_JPEG               = 'image/jpeg',
                 CONTENT_TYPE_IMAGE_PNG                = 'image/png',
                 CONTENT_TYPE_IMAGE_GIF                = 'image/gif',
                 CONTENT_TYPE_IMAGE_WEBP               = 'image/webp',
                 // Downloads
                 CONTENT_TYPE_APPLICATION_DOWNLOAD     = 'application/download',
                 CONTENT_TYPE_APPLICATION_OCTET_STREAM = 'application/octet-stream';

    /**
     * Content charsets.
     * @const string
     */
    public const CONTENT_CHARSET_NA                    = 'n/a',
                 CONTENT_CHARSET_UTF_8                 = 'utf-8',
                 CONTENT_CHARSET_ISO_8859_1            = 'iso-8859-1';

    /**
     * Content.
     * @var ?any
     */
    private $content;

    /**
     * Constructor.
     * @param any|null    $content
     * @param array|null  $contentAttributes
     */
    public function __construct($content = null, array $contentAttributes = null)
    {
        $this->setContent($content);
        $this->setContentAttributes($contentAttributes);
    }

    /**
     * Set content.
     * @param  ?any $content
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     * @return ?any
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content attributes.
     * @param  array|null $contentAttributes
     * @return self
     * @since  4.0
     */
    public function setContentAttributes(array $contentAttributes = null): self
    {
        $this->setAttributes($contentAttributes ?? []);

        return $this;
    }

    /**
     * Get content attributes.
     * @return array
     * @since  4.0
     */
    public function getContentAttributes(): array
    {
        return $this->getAttributes();
    }

    /**
     * Is none.
     * @return bool
     * @since  4.0
     */
    public function isNone(): bool
    {
        return ($this->getAttribute('type') == self::CONTENT_TYPE_NA);
    }

    /**
     * Is text.
     * @return bool
     * @since  4.0
     */
    public function isText(): bool
    {
        return (is_null($this->content) || is_string($this->content))
            && !$this->isNone() && !$this->isFile() && !$this->isImage();
    }

    /**
     * Is file.
     * @return bool
     * @since  4.0
     */
    public function isFile(): bool
    {
        return in_array($this->getAttribute('type'), [
            self::CONTENT_TYPE_APPLICATION_OCTET_STREAM,
            self::CONTENT_TYPE_APPLICATION_DOWNLOAD
        ], true);
    }

    /**
     * Is image.
     * @return bool
     * @since  3.9
     */
    public function isImage(): bool
    {
        return in_array($this->getAttribute('type'), [
            self::CONTENT_TYPE_IMAGE_JPEG,
            self::CONTENT_TYPE_IMAGE_PNG,
            self::CONTENT_TYPE_IMAGE_GIF,
            self::CONTENT_TYPE_IMAGE_WEBP
        ], true);
    }
}
