<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\Response;
use froq\file\File;
use Error;

/**
 * Image Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\ImagePayload
 * @author  Kerem Güneş <k-gun@mail.com>
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
        [$image, $imageType, $modifiedAt] = [
            $this->getContent(), ...$this->getAttributes(['type', 'modifiedAt'])
        ];

        if ($image == null) {
            throw new PayloadException('Image must not be empty');
        } elseif (!is_string($image) && !is_image($image)) {
            throw new PayloadException('Image content must be a valid readable file path, '
                . 'binary string or GdImage, `%s` given', get_type($image));
        } elseif ($imageType == null || !preg_match('~^image/(?:jpeg|png|gif|webp)$~', $imageType)) {
            throw new PayloadException('Invalid image type `%s`, valids are: image/jpeg, '
                . 'image/png, image/gif, image/webp', $imageType ?: 'null');
        }

        if (is_string($image)) {
            $temp = $image;

            // Check if content is a file.
            if (File::isFile($image)) {
                if (File::errorCheck($image, $error)) {
                    throw new PayloadException($error->getMessage(), null, $error->getCode());
                }

                $imageSize = filesize($image);
                $memoryLimit = self::getMemoryLimit($limit);
                if ($memoryLimit > -1 && $imageSize > $memoryLimit) {
                    throw new PayloadException('Given file exceeding `memory_limit` current ini configuration '
                        . 'value (%s)', $limit);
                }

                try {
                    $image = imagecreatefromstring(file_get_contents($image));
                } catch (Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image source, invalid file contents in '
                    . '%s file', $temp);

                $modifiedAt = self::getModifiedAt($temp, $modifiedAt);
            }
            // Convert file to source.
            else {
                try {
                    $image = imagecreatefromstring($image);
                } catch (Error) { $image = null; }

                $image || throw new PayloadException('Failed creating image source, invalid string contents');

                $modifiedAt = self::getModifiedAt('', $modifiedAt);
            }

            unset($temp);
        }

        // Update attributes.
        $this->setAttributes([
            'modifiedAt' => $modifiedAt
        ]);

        return ($content = $image);
    }
}
