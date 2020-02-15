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

namespace froq\http\response\payload;

use froq\util\Strings;
use froq\file\Util as FileUtil;
use froq\http\Response as Container;
use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};

/**
 * Image Payload.
 * @package froq\http\response\Payload
 * @object  froq\http\response\Payload\ImagePayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.9, 4.0
 */
final class ImagePayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param string|resource         $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $container
     */
    public function __construct(int $code, $content, array $attributes = null,
        Container $container = null)
    {
        parent::__construct($code, $content, $attributes, $container);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        [$image, $imageType, $imageModifiedAt] = [
            $this->getContent(), ...$this->getAttributes(['type', 'modifiedAt'])
        ];

        if (!is_string($image) && !is_resource($image)) {
            throw new PayloadException('Image content could be a valid readable file path, '.
                'binary or gd resource, %s given', [gettype($image)]);
        }

        if ($image == null) {
            throw new PayloadException('Image cannot be empty');
        } elseif ($imageType == null || !preg_match('~^image/(?:jpeg|png|gif)$~', $imageType)) {
            throw new PayloadException('Invalid image type "%s", valids are: image/jpeg, '.
                'image/png, image/gif', [$imageType]);
        }

        if (!is_resource($image)) {
            $imageContent = $image;

            // Check if content is a file.
            if (FileUtil::isFile($image)) {
                if (FileUtil::errorCheck($image, $error)) {
                    throw new PayloadException($error->getMessage(), $error->getCode());
                }

                $imageSize = filesize($image);
                $imageSizeLimit = FileUtil::convertBytes(ini_get('memory_limit'));
                if ($imageSize > $imageSizeLimit) {
                    throw new PayloadException('Too large image, check "ini.memory_limit" option');
                }

                // This attribute could be given in attributes (true means auto-set, mime
                // default is true).
                $imageModifiedAt = ($imageModifiedAt === true) ? filemtime($image) : $imageModifiedAt;

                $image = imagecreatefromstring(file_get_contents($image));
            }
            // Convert file to source (binary content accepted).
            elseif (Strings::isBinary($imageContent)) {
                $image = imagecreatefromstring($imageContent);
            }

            if (!is_resource($image)) {
                throw new PayloadException('Failed to create image resource, image content could '.
                    'be a valid readable file path, binary or gd resource');
            }
        }

        if (!is_resource($image) || get_resource_type($image) != 'gd') {
            throw new PayloadException('Invalid image content, image content could be a valid '.
                'readable file path, binary or gd resource');
        }

        // Update attributes.
        $this->setAttributes([
            'modifiedAt' => $imageModifiedAt
        ]);

        return ($content = $image);
    }
}
