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

namespace Froq\Http\Uri;

/**
 * @package    Froq
 * @subpackage Froq\Http\Uri
 * @object     Froq\Http\Uri\UriPath
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class UriPath
{
    /**
     * URI path.
     * @var string
     */
    private $path;

    /**
     * URI segments.
     * @var array
     */
    private $segments = [];

    /**
     * Constructor.
     * @param string $path
     */
    final public function __construct(string $path)
    {
        $this->path = $path;
        if ($path != '') {
            $this->segments = self::generateSegments($path);
        }
    }

    /**
     * Check root.
     * @return bool
     */
    final public function isRoot(): bool
    {
        return ($this->path == '/');
    }

    /**
     * Get path.
     * @return string
     */
    final public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get segment.
     * @param  int $i
     * @param  any $default
     * @return string|null
     */
    final public function getSegment(int $i, $default = null)
    {
        return $this->segments[$i] ?? $default;
    }

    /**
     * Get all segments.
     * @return array
     */
    final public function getSegmentAll(): array
    {
        return $this->segments;
    }

    /**
     * Generate segments.
     * @param  string $path
     * @return array
     */
    final public static function generateSegments(string $path): array
    {
        return array_filter(array_map('trim',
            preg_split('~/+~', $path, -1, PREG_SPLIT_NO_EMPTY)));
    }
}
