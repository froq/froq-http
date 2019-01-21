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

namespace Froq\Http\Request;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\Files
 * @author     Kerem Güneş <k-gun@mail.com>
 * @since      1.0
 */
final class Files
{
    /**
     * Files.
     * @var array
     */
    private $files = [];

    /**
     * Constructor.
     * @param array $files
     */
    public function __construct(array $files)
    {
        foreach ($files as $file) {
            if (is_array($file['name'])) {  // multi-files
                foreach (self::normalizeFilesArray($file) as $file) {
                    $this->files[] = $file;
                }
            } else {  // single file
                $this->files[] = $file;
            }
        }
    }

    /**
     * Get files.
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Normalize files array.
     * @param  array $files
     * @return array
     */
    public static function normalizeFilesArray(array $files): array
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
