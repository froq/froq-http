<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\{Response, message\ContentType};
use froq\file\{Util as FileUtil, File, mime\Mime};
use froq\util\Strings;

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
        [$file, $fileName, $fileMime, $fileExtension, $fileModifiedAt] = [
            $this->getContent(), ...$this->getAttributes(['name', 'mime', 'extension', 'modifiedAt'])
        ];

        if ($file == null) {
            throw new PayloadException("File must not be empty");
        } elseif (!is_string($file) && !is_resource($file)) {
            throw new PayloadException("File content must be a valid readable file path, binary "
                . "or stream resource, '%s' given", gettype($file));
        } elseif ($fileName != null && !preg_match('~^[\w\+\-\.]+$~', $fileName)) {
            throw new PayloadException("File name must not contains non-ascii characters");
        }

        if (!is_resource($file)) {
            $fileContent = $file;

            // Check if content is a file.
            if (FileUtil::isFile($file)) {
                if (FileUtil::errorCheck($file, $error)) {
                    throw new PayloadException($error->getMessage(), null, $error->getCode());
                }

                $fileSize      = filesize($file);
                $fileSizeLimit = FileUtil::convertBytes(ini_get('memory_limit'));
                if ($fileSizeLimit > -1 && $fileSize > $fileSizeLimit) {
                    throw new PayloadException("Too large file, check 'ini.memory_limit' option "
                        . "(current ini value: %s)", ini_get('memory_limit'));
                }

                // Those attributes may be given in attributes (true means auto-set, mime default is true).
                $fileMime       = (($fileMime ?? true) === true) ? File::getType($file) : $fileMime;
                $fileModifiedAt = ($fileModifiedAt === true) ? filemtime($file) : $fileModifiedAt;

                $file = fopen($file, 'rb');
                if (!$file) {
                    throw new PayloadException('Failed to create file resource, file content must '
                        . 'be a valid readable file path, binary or stream resource [error: %s]', '@error');
                }

                $fileName      = $fileName ?: pathinfo($fileContent, PATHINFO_FILENAME);
                $fileExtension = $fileExtension ?: ($fileMime ? Mime::getExtensionByType($fileMime)
                                                              : Mime::getExtension($fileContent));
            }
            // Convert file to source (binary content accepted).
            elseif (Strings::isBinary($fileContent)) {
                $file = tmpfile();
                fwrite($file, $fileContent);

                $fileName      = $fileName ?: crc32($fileContent);
                $fileExtension = $fileExtension ?: ($fileMime ? Mime::getExtensionByType($fileMime)
                                                              : Mime::getExtensionByType(mime_content_type($file)));
            }
        }

        if (!is_resource($file) || get_resource_type($file) != 'stream') {
            throw new PayloadException('Invalid file content, file content must be a valid '
                . 'readable file path, binary or stream resource');
        }

        $fileSize      = $fileSize ?? fstat($file)['size'];
        $fileName      = $fileName ?: pathinfo(stream_get_meta_data($file)['uri'], PATHINFO_FILENAME)
                                   ?: crc32(fread($file, $fileSize));
        $fileExtension = $fileExtension ?: ($fileMime ? Mime::getExtensionByType($fileMime) : (
                                                        Mime::getExtension($fileName) ?:
                                                        Mime::getExtensionByType(mime_content_type($file))));

        // Ensure that all file name characters are safe.
        $fileName = preg_replace('~[^\w\+\-\.]+~', ' ',
            ($fileExtension != null) // Remove duplicated extensions.
                ? preg_replace('~\.'. $fileExtension .'$~', '', $fileName) .'.'. $fileExtension
                : $fileName
        );

        // Update attributes.
        $this->setAttributes([
            'size' => $fileSize, 'name'       => $fileName,
            'mime' => $fileMime, 'modifiedAt' => $fileModifiedAt
        ]);

        return ($content = $file);
    }
}
