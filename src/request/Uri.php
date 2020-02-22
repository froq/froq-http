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

use froq\http\{Url, UrlException};
use froq\http\request\UriException;

/**
 * Uri.
 * @package froq\http\request
 * @object  froq\http\request\Uri
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Uri extends Url
{
    /**
     * Segments.
     * @var array
     */
    private array $segments = [];

    /**
     * Segments root.
     * @var ?string
     */
    private ?string $segmentsRoot;

    /**
     * Constructor.
     * @param  array|string $source
     * @throws froq\http\request\UriException
     */
    public function __construct($source)
    {
        try {
            parent::__construct($source, ['path', 'query', 'queryParams', 'fragment']);
        } catch (UrlException $e) {
            throw new UriException($e);
        }

        $this->readOnly(true); // Lock.
    }

    /**
     * Segment.
     * @param  int $i
     * @param  any $default
     * @return any
     */
    public function segment(int $i, $default = null)
    {
        return $this->segments[$i] ?? $default;
    }

    /**
     * Segments.
     * @return array
     */
    public function segments(): array
    {
        return $this->segments;
    }

    /**
     * Get segments root.
     * @return ?string
     */
    public function segmentsRoot(): ?string
    {
        return $this->segmentsRoot ?? null;
    }

    /**
     * Generate segments.
     * @param  string|null $root
     * @return void
     * @throws froq\http\request\UriException
     */
    public function generateSegments(string $root = null): void
    {
        $path = rawurldecode($this->get('path') ?: '');
        if ($path && $path != '/') {
            // Drop root if exists.
            if ($root && $root != '/') {
                $root = '/'. trim($root, '/'). '/';

                // Prevent wrong generate action.
                if (strpos($path, $root) !== 0) {
                    throw new UriException('URI path "%s" has no root such "%s"', [$path, $root]);
                }

                $path = substr($path, strlen($root));

                // Update segments root.
                $this->segmentsRoot = $root;
            }

            $segments = array_map('trim', preg_split('~/+~', $path, -1, 1));

            foreach ($segments as $i => $segment) {
                // Push index next (skip 0), so provide a (1,2,3) array for segments.
                $this->segments[$i + 1] = $segment;
            }
        }
    }
}
