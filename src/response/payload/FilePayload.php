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
use froq\http\message\Body;
use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};

/**
 * File Payload.
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\FilePayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 */
final class FilePayload extends Payload implements PayloadInterface
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
        $attributes['type'] = Body::CONTENT_TYPE_APPLICATION_OCTET_STREAM;

        parent::__construct($code, $content, $attributes, $container);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        [$file, $fileName, $fileMime, $fileExtension, $fileModifiedAt] = [
            $this->getContent(), ...$this->getAttributes(['name', 'mime', 'extension', 'modifiedAt'])
        ];

        if (!is_string($file) && !is_resource($file)) {
            throw new PayloadException('File content could be a valid and readable file path, '.
                'binary or stream resource, "%s" given', [gettype($file)]);
        }

        if ($file == null) {
            throw new PayloadException('File cannot be empty');
        } elseif ($fileName != null && !preg_match('~^[a-zA-Z0-9\+\-\.]+$~', $fileName)) {
            throw new PayloadException('File name cannot contains non-ascii characters');
        }

        if (!is_resource($file)) {
            $fileContent = $file;

            // Check if content is a file.
            if (FileUtil::isFile($file)) {
                if (FileUtil::errorCheck($file, $error)) {
                    throw new PayloadException($error->getMessage(), $error->getCode());
                }

                $fileSize = filesize($file);
                $fileSizeLimit = FileUtil::convertBytes(ini_get('memory_limit'));
                if ($fileSize > $fileSizeLimit) {
                    throw new PayloadException('Too large file, check "ini.memory_limit" option');
                }

                // Those attributes could be given in attributes (true means auto-set, mime
                // default is true).
                $fileMime = (($fileMime ?? true) === true) ? FileUtil::getType($file) : $fileMime;
                $fileModifiedAt = ($fileModifiedAt === true) ? filemtime($file) : $fileModifiedAt;

                $file =@ fopen($file, 'rb');
                if ($file === false) {
                    throw new PayloadException('Failed to create file resource, file content '.
                        'could be a valid and readable file path, binary or stream resource');
                }
                $fileName = $fileName ?: basename($this->getContent());
            }
            // Convert file to source (binary content accepted).
            elseif (Strings::isBinary($fileContent)) {
                $file = fopen('php://temp', 'r+b');
                $fileSize = fstat($file)['size'];
                $fileName = $fileName ?: hash('crc32', $fileContent);
                fwrite($file, $fileContent);
            }
        }

        if (!is_resource($file) || get_resource_type($file) != 'stream') {
            throw new PayloadException('Invalid file content, file content could be a valid and '.
                'readable file path, binary or stream resource');
        } else {
            $fileSize = fstat($file)['size'];
            $fileName = $fileName ?: basename(stream_get_meta_data($file)['uri'] ?? '')
                                  ?: hash('crc32', fread($file, $fileSize));
        }

        // Ensure that all $fileName characters are printable (safe).
        $fileName = rawurlencode(preg_replace('~[^a-zA-Z0-9\+\-\.]+~', ' ',
            ($fileExtension == null) ? $fileName : $fileName .'.'. $fileExtension
        ));

        // Update attributes.
        $this->setAttributes([
            'size' => $fileSize, 'name' => $fileName,
            'mime' => $fileMime, 'modifiedAt' => $fileModifiedAt
        ]);

        return ($content = $file);
    }
}
