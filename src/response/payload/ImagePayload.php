<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};
use froq\file\File;

/**
 * A payload class for sending images as response content with attributes.
 *
 * @package froq\http\response\payload
 * @class   froq\http\response\payload\ImagePayload
 * @author  Kerem Güneş
 * @since   3.9
 */
class ImagePayload extends Payload implements PayloadInterface
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
     * @inheritDoc froq\http\response\payload\PayloadInterface
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
            throw new PayloadException('Invalid image type %q [valids: %A]',
                [$imageType ?: 'null', ContentType::imageTypes()]);
        }

        // Direct image reads.
        if ($direct && !$type->isString()) {
            throw new PayloadException('Image content must be a valid readable file path '.
                'when "direct" option is true, %s given', $type);
        }

        if (!$direct && $type->isString()) {
            $temp = $image;

            // Check if content is a file.
            if (File::isFile($image)) {
                if (File::errorCheck($image, $error)) {
                    throw new PayloadException($error);
                }

                $imageSize   = filesize($image);
                $memoryLimit = $this->getMemoryLimit($limit);
                if ($memoryLimit > -1 && $imageSize > $memoryLimit) {
                    throw new PayloadException('Given image exceeding "memory_limit" current ini '.
                        'configuration value (%s)', $limit);
                }

                try {
                    $image = imagecreatefromstring(file_get_contents($temp));
                } catch (\Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image resource [error: @error]');

                $modifiedAt = $this->getModifiedAt($temp, $modifiedAt);
            }
            // Convert content to source.
            else {
                try {
                    $image = imagecreatefromstring($image);
                } catch (\Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image resource [error: @error]');

                $modifiedAt = $this->getModifiedAt('', $modifiedAt);
            }

            unset($temp);
        }
        // Image may be GdImage.
        elseif (!$type->isImage()) {
            if (File::errorCheck($image, $error)) {
                throw new PayloadException($error);
            }

            $modifiedAt = $this->getModifiedAt($image, $modifiedAt);
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
