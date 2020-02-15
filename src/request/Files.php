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

namespace froq\http\request;

/**
 * Files.
 * @package froq\http\request
 * @object  froq\http\request\Files
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0, 4.0
 * @static
 */
final class Files
{
    /**
     * All.
     * @return array
     * @since  4.0
     */
    public static function all(): array
    {
        return self::normalizeFiles();
    }

    /**
     * Normalize files (two-dims only).
     * @param  array|null $files
     * @return array
     */
    public static function normalizeFiles(array $files = null): array
    {
        $files  = $files ?? $_FILES;
        $return = [];

        foreach ($files as $id => $file) {
            if (!isset($file['name'])) {
                continue;
            }
            if (!is_array($file['name'])) {
                $return[] = $file + ['_id' => $id]; // Add input name.
                continue;
            }

            foreach ($file['name'] as $i => $name) {
                $return[] = [
                    'name'     => $name,
                    'type'     => $file['type'][$i],
                    'tmp_name' => $file['tmp_name'][$i],
                    'error'    => $file['error'][$i],
                    'size'     => $file['size'][$i],
                ] + ['_id' => $id .'['. $i .']']; // Add input name.
            }
        }

        return $return;
    }
}
