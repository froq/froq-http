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

use froq\http\HttpException;

/**
 * Uri.
 * @package froq\http\request
 * @object  froq\http\request\Uri
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Uri
{
    /**
     * Source.
     * @var string
     */
    private $source;

    /**
     * Source data.
     * @var string
     */
    private $sourceData;

    /**
     * Source data keys.
     * @var array
     */
    private $sourceDataKeys = [
        'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'
    ];

    /**
     * Segments.
     * @var array
     */
    private $segments = [];

    /**
     * Segments root.
     * @var string
     */
    private $segmentsRoot;

    /**
     * Constructor.
     * @param string|array $source
     */
    public function __construct($source = null)
    {
        if ($source != null) {
            $this->source = $source;
            $sourceType = gettype($source);
            if ($sourceType == 'string') {
                $this->sourceData = parse_url($source);
            } elseif ($sourceType == 'array') {
                foreach ($source as $key => $value) {
                    $this->__call('set'. $key, [$value]);
                }
            } else {
                throw new HttpException(sprintf("Only 'string/array' type sources allowed for %s ".
                    "object '{$sourceType}' given", self::class));
            }
        }
    }

    /**
     * String magic.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Call magic.
     * @param  string     $method
     * @param  array|null $methodArguments
     * @return int|string|self
     * @throws froq\http\HttpException
     */
    public function __call(string $method, array $methodArguments = null)
    {
        $cmd = substr($method, 0, 3);
        if ($cmd != 'set' && $cmd != 'get') {
            throw new HttpException(sprintf("Only 'set/get' methods allowed for %s::__call() magic",
                self::class));
        }

        $key = strtolower(substr($method, 3));
        if (!in_array($key, $this->sourceDataKeys)) {
            throw new HttpException(sprintf("No field such '%s' exists on %s", $key, self::class));
        }

        // setters
        if ($cmd == 'set') {
            // sorry babe, we love hard..
            if (!array_key_exists(0, $methodArguments)) {
                throw new HttpException(sprintf("No argument given for %s::set%s()", self::class, ucfirst($key)));
            }
            $value = $methodArguments[0];

            if ($key == 'port' && !is_int($value)) {
                throw new HttpException(sprintf("'port' field must be an integer for %s", self::class));
            } elseif ($value !== null && !is_string($value)) {
                throw new HttpException(sprintf("All fields must be string (except 'port') for %s", self::class));
            }

            $this->sourceData[$key] = $value;

            return $this;
        }

        if ($cmd == 'get') { // getters
            return $this->sourceData[$key] ?? null;
        }
    }

    /**
     * Segment.
     * @param  int $i
     * @param  any $valueDefault
     * @return any
     */
    public function segment(int $i, $valueDefault = null)
    {
        return $this->segments[$i] ?? $valueDefault;
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
     * Segment arguments.
     * @param  int $offset
     * @return array
     */
    public function segmentArguments(int $offset): array
    {
        return array_slice($this->segments, $offset);
    }

    /**
     * Generate segments.
     * @param  string $root
     * @return void
     */
    public function generateSegments(string $root = null): void
    {
        $path = rawurldecode($this->sourceData['path'] ?? '');
        if ($path && $path != '/') {
            // drop root if exists
            if ($root && $root != '/') {
                $root = '/'. trim($root, '/'). '/';
                // prevent wrong generate action
                if (strpos($path, $root) === false) {
                    throw new HttpException("Uri path '{$path}' has no root such '{$root}'");
                }

                $path = substr($path, strlen($root));

                // update segments root
                $this->segmentsRoot = $root;
            }

            $this->segments = array_map('trim', preg_split('~/+~', $path, -1, PREG_SPLIT_NO_EMPTY));
        }
    }

    /**
     * To array.
     * @return array
     */
    public function toArray(): array
    {
        return $this->sourceData;
    }

    /**
     * To string.
     * @param  array|null $excludedKeys
     * @return string
     */
    public function toString(array $excludedKeys = null): string
    {
        $sourceData = $this->sourceData;
        if ($excludedKeys != null) {
            $sourceData = array_filter($sourceData, function ($key) use ($excludedKeys) {
                return !in_array($key, $excludedKeys);
            }, 2);
        }

        $return = '';

        !empty($sourceData['scheme']) && $return .= $sourceData['scheme'] . '://';
        if (!empty($sourceData['user']) || !empty($sourceData['pass'])) {
            !empty($sourceData['user']) && $return .= $sourceData['user'];
            !empty($sourceData['pass']) && $return .= ':'. $sourceData['pass'];
            $return .= '@';
        }
        !empty($sourceData['host']) && $return .= $sourceData['host'];
        !empty($sourceData['port']) && $return .= ':'. $sourceData['port'];
        !empty($sourceData['path']) && $return .= $sourceData['path'];
        !empty($sourceData['query']) && $return .= '?'. $sourceData['query'];
        !empty($sourceData['fragment']) && $return .= '#'. $sourceData['fragment'];

        return $return;
    }
}
