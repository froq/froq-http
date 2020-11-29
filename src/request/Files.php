<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

/**
 * Files.
 *
 * @package froq\http\request
 * @object  froq\http\request\Files
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
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
