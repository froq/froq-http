<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\message;

use froq\http\message\ContentType;
use froq\common\trait\AttributeTrait;

/**
 * Body.
 *
 * @package froq\http\message
 * @object  froq\http\message\Body
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Body
{
    /**
     * @see froq\common\trait\AttributeTrait
     * @since 4.0
     */
    use AttributeTrait;

    /** @var any|null */
    private $content;

    /**
     * Constructor.
     *
     * @param any|null   $content
     * @param array|null $attributes
     */
    public function __construct($content = null, array $attributes = null)
    {
        $this->content    = $content;
        $this->attributes = $attributes ?? [];
    }

    /**
     * Set content.
     *
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
     *
     * @return any|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Is none.
     *
     * @return bool
     * @since  4.0
     */
    public function isNone(): bool
    {
        return $this->getAttribute('type') == ContentType::NA;
    }

    /**
     * Is text.
     *
     * @return bool
     * @since  4.0
     */
    public function isText(): bool
    {
        return (is_null($this->content) || is_string($this->content))
            && !($this->isNone() || $this->isFile() || $this->isImage());
    }

    /**
     * Is file.
     *
     * @return bool
     * @since  4.0
     */
    public function isFile(): bool
    {
        return in_array($this->getAttribute('type'), [
            ContentType::APPLICATION_OCTET_STREAM,
            ContentType::APPLICATION_DOWNLOAD
        ]);
    }

    /**
     * Is image.
     *
     * @return bool
     * @since  3.9
     */
    public function isImage(): bool
    {
        return in_array($this->getAttribute('type'), [
            ContentType::IMAGE_JPEG, ContentType::IMAGE_PNG,
            ContentType::IMAGE_GIF, ContentType::IMAGE_WEBP
        ]);
    }
}
