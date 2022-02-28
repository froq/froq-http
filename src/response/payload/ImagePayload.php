<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};
use froq\file\File;

/**
 * Image Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\ImagePayload
 * @author  Kerem Güneş
 * @since   3.9
 */
final class ImagePayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     *
     * @param int                     $code
     * @param string|GdImage          $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null, Response $response = null)
    {
        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        [$image, $imageType, $modifiedAt, $direct] = [
            $this->getContent(), ...$this->getAttributes(['type', 'modifiedAt', 'direct'])
        ];

        $type = new \Type($image);

        if (!$image) {
            throw new PayloadException('Image empty');
        } elseif (!$type->isString() && !$type->isImage()) {
            throw new PayloadException('Image content must be a valid readable file path, '.
                'binary string or GdImage, %s given', $type);
        } elseif (!$imageType || !$this->isValidImageType($imageType)) {
            throw new PayloadException('Invalid image type `%s` [valids: %s]',
                [$imageType ?: 'null', join(',', ContentType::imageTypes())]);
        }

        // Direct image reads.
        if ($direct && !$type->isString()) {
            throw new PayloadException('Image content must be a valid readable file path '.
                'when `direct` option is true, %s given', $type);
        }

        if (!$direct && $type->isString()) {
            $temp = $image;

            // Check if content is a file.
            if (File::isFile($image)) {
                if (File::errorCheck($image, $error)) {
                    throw new PayloadException($error->message, code: $error->code, cause: $error);
                }

                $imageSize   = filesize($image);
                $memoryLimit = self::getMemoryLimit($limit);
                if ($memoryLimit > -1 && $imageSize > $memoryLimit) {
                    throw new PayloadException('Given image exceeding `memory_limit` current ini '.
                        'configuration value (%s)', $limit);
                }

                try {
                    $image = imagecreatefromstring(file_get_contents($image));
                } catch (\Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image resource [error: @error]');

                $modifiedAt = self::getModifiedAt($temp, $modifiedAt);
            }
            // Convert content to source.
            else {
                try {
                    $image = imagecreatefromstring($image);
                } catch (\Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image resource [error: @error]');

                $modifiedAt = self::getModifiedAt('', $modifiedAt);
            }

            unset($temp);
        }
        // Image may be GdImage.
        elseif (!$type->isImage()) {
            if (File::errorCheck($image, $error)) {
                throw new PayloadException($error->message, code: $error->code, cause: $error);
            }

            $modifiedAt = self::getModifiedAt($image, $modifiedAt);
        }

        // Update attributes.
        $this->setAttributes([
            'modifiedAt' => $modifiedAt,
            'direct'     => $direct
        ]);

        return ($content = $image);
    }

    /**
     * Valid iamge-type checker.
     */
    private function isValidImageType(mixed $imageType): bool
    {
        return preg_test('~^image/(?:jpeg|webp|png|gif)$~', (string) $imageType);
    }
}
