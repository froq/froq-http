<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};
use froq\file\{File, mime\Mime};

/**
 * A payload class for sending files as response content with attributes.
 *
 * @package froq\http\response\payload
 * @class   froq\http\response\payload\FilePayload
 * @author  Kerem Güneş
 * @since   4.0
 */
class FilePayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     *
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
     * @inheritDoc froq\http\response\payload\PayloadInterface
     */
    public function handle()
    {
        [$file, $fileName, $fileMime, $fileExtension, $modifiedAt, $direct] = [
            $this->getContent(), ...$this->getAttributes(['name', 'mime', 'extension', 'modifiedAt', 'direct'])
        ];

        $type = new \Type($file);

        if (!$file) {
            throw new PayloadException('File empty');
        } elseif (!$type->isString() && !$type->isStream()) {
            throw new PayloadException('File content must be a valid readable file path, '.
                'binary string or stream, %s given', $type);
        } elseif ($fileName && !$this->isValidFileName($fileName)) {
            throw new PayloadException('File name must not contain non-ascii characters');
        }

        // Direct file reads.
        if ($direct && !$type->isString()) {
            throw new PayloadException('File content must be a valid readable file path '.
                'when "direct" option is true, %s given', $type);
        }

        if (!$direct && $type->isString()) {
            $temp = $file;

            // Check if content is a file.
            if (File::isFile($file)) {
                if (File::errorCheck($file, $error)) {
                    throw new PayloadException($error);
                }

                $fileSize    = filesize($file);
                $memoryLimit = $this->getMemoryLimit($limit);
                if ($memoryLimit > -1 && $fileSize > $memoryLimit) {
                    throw new PayloadException('Given file exceeding "memory_limit" current ini '.
                        'configuration value (%s)', $limit);
                }

                try {
                    $file = fopen($file, 'rb');
                } catch (\Error) { $file = null; }

                $file || throw new PayloadException('Failed creating file resource [error: @error]');

                $fileName   = $fileName ?: filename($temp, true);
                $modifiedAt = $this->getModifiedAt($temp, $modifiedAt);
            }
            // Convert content to source.
            else {
                try {
                    fwrite($file = tmpfile(), $temp);
                } catch (\Error) { $file = null; }

                $file || throw new PayloadException('Failed creating file resource [error: @error]');

                $fileName   = strval($fileName ?: crc32($temp));
                $modifiedAt = $this->getModifiedAt('', $modifiedAt);
            }

            unset($temp);
        }
        // File may be stream.
        elseif (!$type->isStream()) {
            if (File::errorCheck($file, $error)) {
                throw new PayloadException($error);
            }

            $fileName   = $fileName ?: filename($file, true);
            $modifiedAt = $this->getModifiedAt($file, $modifiedAt);

            $fileMime   = $fileMime ?: filemime($file);
            $fileSize   = $fileSize ?: filesize($file);
        }

        $fileName = $fileName ?: fmeta($file)['uri'];
        $fileMime = $fileMime ?: filemime($fileName);
        $fileSize = $fileSize ?: fstat($file)['size'];

        // Extract file name & extension.
        if ($fileName) {
            $name     = $fileName;
            $fileName = filename($name);
            if (str_contains($name, '.')) {
                $fileExtension = file_extension($name);
            }
        }

        // Add extension to file name.
        $fileName = $fileName .'.'. ($fileExtension ?? (
            $fileMime ? Mime::getExtensionByType($fileMime)
                      : File::getExtension($fileName)
        ));

        // Update attributes.
        $this->setAttributes([
            'name'   => $fileName, 'mime'       => $fileMime,
            'size'   => $fileSize, 'modifiedAt' => $modifiedAt,
            'direct' => $direct
        ]);

        return ($content = $file);
    }

    /**
     * Valid file-name checker.
     */
    private function isValidFileName(mixed $fileName): bool
    {
        return preg_test('~^[\w\+\-\.]+$~', (string) $fileName);
    }
}
