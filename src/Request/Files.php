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

use Froq\Http\Request\Files\File;

/**
 * @package    Froq
 * @subpackage Froq\Http\Request
 * @object     Froq\Http\Request\Files
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Files
{
    /**
     * Get params.
     * @var array
     */
    private $files = [];

    /**
     * Constructor.
     * @param array $files
     */
    final public function __construct(array $files = [])
    {
        $this->setFiles($files);
    }

    /**
     * Set files.
     * @param  array $files
     * @return self
     */
    final public function setFiles(array $files = []): self
    {
        if (!empty($files)) {
            // single file
            if (!isset($files['name'][0])) {
                $this->files[] = new File($files);
            } else {
                // multi-files
                $files = self::normalizeFilesArray($files);
                foreach ($files as $file) {
                    $this->files[] = new File($file);
                }
            }
        }

        return $this;
    }

    /**
     * Get files.
     * @return array
     */
    final public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Normalize files array.
     * @param  array $files
     * @return array
     */
    final public static function normalizeFilesArray(array $files): array
    {
        $return = [];
        foreach ($files as $i => $file) {
            foreach ($file as $key => $value) {
                $return[$key][$i] = $value;
            }
        }

        return $return;
    }
}
