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

use froq\common\interfaces\Stringable;
use froq\collection\ComponentCollection;
use froq\http\request\UriException;

/**
 * Uri.
 * @package froq\http\request
 * @object  froq\http\request\Uri
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Uri extends ComponentCollection implements Stringable
{
    /**
     * Source.
     * @var string
     */
    private string $source;

    /**
     * Segments.
     * @var array
     */
    private array $segments = [];

    /**
     * Segments root.
     * @var ?string
     */
    private ?string $segmentsRoot = null;

    /**
     * Constructor.
     * @param  string $source
     * @throws froq\http\request\UriException
     */
    public function __construct(string $source)
    {
        // Set component names.
        parent::__construct(['scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment']);

        $this->source = $source;

        $components = parse_url($source);
        if ($components == null) {
            throw new UriException('Invalid URI source');
        }

        foreach ($components as $name => $value) {
            $this->set($name, $value);
        }
        $this->readOnly(true);
    }

    /**
     * Get source.
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
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
        return $this->segmentsRoot;
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

    /**
     * @inheritDoc froq\common\interfaces\Stringable
     */
    public function toString(): string
    {
        $ret = '';

        @ ['scheme' => $scheme, 'host'     => $host, 'port' => $port,
           'user'   => $user,   'pass'     => $pass, 'path' => $path,
           'query'  => $query,  'fragment' => $fragment] = $this->toArray();

        if ($scheme) {
            $ret .= $scheme . '://';
        }
        if ($user || $pass) {
            $user && $ret .= $user;
            $pass && $ret .= ':'. $pass;
            $ret .= '@';
        }

        $host     && $ret .= $host;
        $port     && $ret .= ':'. $port;
        $path     && $ret .= $path;
        $query    && $ret .= '?'. $query;
        $fragment && $ret .= '#'. $fragment;

        return $ret;
    }
}
