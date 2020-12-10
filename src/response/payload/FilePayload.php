<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\{Response, message\ContentType};
use froq\file\{File, mime\Mime};

/**
 * File Payload.
 *
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
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null, Response $response = null)
    {
        $attributes['type'] = ContentType::APPLICATION_OCTET_STREAM;

        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        [$file, $fileName, $fileMime, $fileExtension, $modifiedAt] = [
            $this->getContent(),
            ...$this->getAttributes(['name', 'mime', 'extension', 'modifiedAt'])
        ];

        if ($file == null) {
            throw new PayloadException('File must not be empty');
        } elseif (!is_string($file) && !is_stream($file)) {
            throw new PayloadException('File content must be a valid readable file path, '
                . 'binary string or stream, `%s` given', get_type($file));
        } elseif ($fileName != null && !preg_match('~^[\w\+\-\.]+$~', $fileName)) {
            throw new PayloadException('File name must not contains non-ascii characters');
        }

        if (is_string($file)) {
            $temp = $file;

            // Check if content is a file.
            if (File::isFile($file)) {
                if (File::errorCheck($file, $error)) {
                    throw new PayloadException($error->getMessage(), null, $error->getCode());
                }

                $fileSize = filesize($file);
                $memoryLimit = self::getMemoryLimit($limit);
                if ($memoryLimit > -1 && $imageSize > $memoryLimit) {
                    throw new PayloadException('Given file exceeding `memory_limit` current ini configuration '
                        . 'value (%s)', $limit);
                }

                try {
                    $file = fopen($file, 'rb');
                } catch (Error) { $file = null; }

                $file || throw new PayloadException('Failed creating file resource, file content must be a  '
                    . 'valid readable file path');

                $fileName = $fileName ?: file_name($temp);
                $modifiedAt = self::getModifiedAt($temp, $modifiedAt);
            }
            // Convert file to source.
            else {
                try {
                    $file = tmpfile();
                    $file && fwrite($file, $temp);
                } catch (Error) { $file = null; }

                $file || throw new PayloadException('Failed creating file resource, cannot write temp-file');

                $fileName = strval($fileName ?: crc32($temp));
                $modifiedAt = self::getModifiedAt('', $modifiedAt);
            }

            unset($temp);
        }

        // Extract file name & extension.
        if ($fileName != null) {
            $name = $fileName;
            $fileName = file_name($name);
            $fileExtension = strstr($name, '.') ? file_extension($name) : $fileExtension;
        }

        // Ensure all needed stuff.
        $info = finfo($file);
        $fileName = $fileName ?: file_name($info['meta']['uri'] ?? '') ?: crc32(freads($file));
        $fileMime = $fileMime ?: mime_content_type($file);
        $fileSize = $fileSize ?: $info['size'];

        // Add extension to file name.
        $fileName = $fileName .'.'. ($fileExtension ?: (
            $fileMime ? Mime::getExtensionByType($fileMime)
                      : Mime::getExtension($fileName)
        ));

        // Update attributes.
        $this->setAttributes([
            'name' => $fileName, 'mime' => $fileMime,
            'size' => $fileSize, 'modifiedAt' => $modifiedAt
        ]);

        return ($content = $file);
    }
}
