<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Froq\Http\Request;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\File
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class File
{
    /**
     * Name.
     * @var string
     */
    private $name;

    /**
     * Tmp name.
     * @var string
     */
    private $nameTmp;

    /**
     * Type.
     * @var string
     */
    private $type;

    /**
     * Size.
     * @var int
     */
    private $size;

    /**
     * Error.
     * @var int
     */
    private $error;

    /**
     * Error string.
     * @var string
     */
    private $errorString;

    /**
     * Errors.
     * @var array
     */
    private static $errors = [
        0 => '',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        3 => 'The uploaded file was only partially uploaded.',
        4 => 'No file was uploaded.',
        6 => 'Missing a temporary folder.',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    ];

    /**
     * Constructor.
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            isset($data['name']) &&
                $this->setName($data['name']);
            isset($data['tmp_name']) &&
                $this->setNameTmp($data['tmp_name']);
            isset($data['type']) &&
                $this->setType($data['type']);
            isset($data['size']) &&
                $this->setSize($data['size']);
            isset($data['error']) &&
                $this->setError($data['error']) &&
                $this->setErrorString(self::$errors[$this->error] ?? 'Unknown error.');
        }
    }

    /**
     * Set name.
     * @param  string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set temp. name.
     * @param  string $nameTmp
     * @return self
     */
    public function setNameTmp(string $nameTmp): self
    {
        $this->nameTmp = $nameTmp;

        return $this;
    }

    /**
     * Get temp. name.
     * @return ?string
     */
    public function getNameTmp(): ?string
    {
        return $this->nameTmp;
    }

    /**
     * Set type.
     * @param  string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     * @return ?string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set size.
     * @param  int $size
     * @return self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     * @return ?int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Set error.
     * @param  int $error
     * @return self
     */
    public function setError(int $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error.
     * @return ?int
     */
    public function getError(): ?int
    {
        return $this->error;
    }

    /**
     * Set error string.
     * @param  string $errorString
     * @return self
     */
    public function setErrorString(string $errorString): self
    {
        $this->errorString = $errorString;

        return $this;
    }

    /**
     * Get error string.
     * @return ?string
     */
    public function getErrorString(): ?string
    {
        return $this->errorString;
    }

    /**
     * To array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'tmp_name'    => $this->nameTmp,
            'type'        => $this->type,
            'size'        => $this->size,
            'error'       => $this->error,
            'errorString' => $this->errorString
        ];
    }

    /**
     * Normalize file name.
     * @param  string $fileName
     * @return string
     */
    public static function normalizeFileName(string $fileName): string
    {
        return preg_replace('~[^\w-.]~', '', $fileName);
    }
}
