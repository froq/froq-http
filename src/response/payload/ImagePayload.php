<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\Response;
use froq\file\Util as FileUtil;
use froq\util\Strings;

/**
 * Image Payload.
 *
 * @package froq\http\response\Payload
 * @object  froq\http\response\Payload\ImagePayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.9
 */
final class ImagePayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param string|resource         $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null,
        Response $response = null)
    {
        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        [$image, $imageType, $imageModifiedAt] = [
            $this->getContent(), ...$this->getAttributes(['type', 'modifiedAt'])
        ];

        if ($image == null) {
            throw new PayloadException('Image must not be empty');
        } elseif (!is_string($image) && !is_resource($image)) {
            throw new PayloadException('Image content must be a valid readable file path, '.
                'binary or gd resource, "%s" given', [gettype($image)]);
        } elseif ($imageType == null || !preg_match('~^image/(?:jpeg|png|gif|webp)$~', $imageType)) {
            throw new PayloadException('Invalid image type "%s", valids are: image/jpeg, '.
                'image/png, image/gif, image/webp', [$imageType]);
        }

        if (!is_resource($image)) {
            $imageContent = $image;

            // Check if content is a file.
            if (FileUtil::isFile($image)) {
                if (FileUtil::errorCheck($image, $error)) {
                    throw new PayloadException($error->getMessage(), null, $error->getCode());
                }

                $imageSize      = filesize($image);
                $imageSizeLimit = FileUtil::convertBytes(ini_get('memory_limit'));
                if ($imageSizeLimit > -1 && $imageSize > $imageSizeLimit) {
                    throw new PayloadException('Too large image, check "ini.memory_limit" option '.
                        '(current ini value: %s)', [ini_get('memory_limit')]);
                }

                // This attribute may be given in attributes (true means auto-set, mime default is true).
                $imageModifiedAt = ($imageModifiedAt === true) ? filemtime($image) : $imageModifiedAt;

                $image = imagecreatefromstring(file_get_contents($image));
            }
            // Convert file to source (binary content accepted).
            elseif (Strings::isBinary($imageContent)) {
                $image = imagecreatefromstring($imageContent);
            }

            if (!is_resource($image)) {
                throw new PayloadException('Failed to create image resource, image content must '.
                    'be a valid readable file path, binary or gd resource');
            }
        }

        if (!is_resource($image) || get_resource_type($image) != 'gd') {
            throw new PayloadException('Invalid image content, image content must be a valid '.
                'readable file path, binary or gd resource');
        }

        // Update attributes.
        $this->setAttributes([
            'modifiedAt' => $imageModifiedAt
        ]);

        return ($content = $image);
    }
}
