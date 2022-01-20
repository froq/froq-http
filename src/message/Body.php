<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
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
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Body
{
    /**
     * @see froq\common\trait\AttributeTrait
     * @since 4.0
     */
    use AttributeTrait;

    /** @var mixed|null */
    private mixed $content = null;

    /**
     * Constructor.
     *
     * @param mixed|null $content
     * @param array|null $attributes
     */
    public function __construct(mixed $content = null, array $attributes = null)
    {
        $this->content    = $content;
        $this->attributes = $attributes ?? [];
    }

    /**
     * Set content.
     *
     * @param  mixed $content
     * @return self
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Set content type.
     *
     * @param  string $type
     * @return self
     * @since  6.0
     */
    public function setContentType(string $type): self
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * Get content type.
     *
     * @return string|null
     * @since  6.0
     */
    public function getContentType(): string|null
    {
        return $this->getAttribute('type');
    }

    /**
     * Set content charset.
     *
     * @param  string $charset
     * @return self
     * @since  6.0
     */
    public function setContentCharset(string $charset): self
    {
        $this->setAttribute('charset', $charset);

        return $this;
    }

    /**
     * Get content charset.
     *
     * @return string|null
     * @since  6.0
     */
    public function getContentCharset(): string|null
    {
        return $this->getAttribute('charset');
    }

    /**
     * Is na.
     *
     * @return bool
     * @since  4.0
     */
    public function isNa(): bool
    {
        return ($this->getContentType() == ContentType::NA);
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
            && (!$this->isNa() && !$this->isFile() && !$this->isImage());
    }

    /**
     * Is file.
     *
     * @return bool
     * @since  4.0
     */
    public function isFile(): bool
    {
        return in_array($this->getContentType(), [
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
        return in_array($this->getContentType(), [
            ContentType::IMAGE_JPEG, ContentType::IMAGE_PNG,
            ContentType::IMAGE_GIF, ContentType::IMAGE_WEBP
        ]);
    }
}
