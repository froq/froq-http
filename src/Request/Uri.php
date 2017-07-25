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
 * @object     Froq\Http\Request\Uri
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Uri
{
    /**
     * Source.
     * @var string
     */
    private $source;

    /**
     * Scheme.
     * @var string
     */
    private $scheme;

    /**
     * Host.
     * @var string
     */
    private $host;

    /**
     * Port.
     * @var int
     */
    private $port;

    /**
     * User.
     * @var string
     */
    private $user;

    /**
     * Pass.
     * @var string
     */
    private $pass;

    /**
     * Path.
     * @var string
     */
    private $path;

    /**
     * Query.
     * @var string
     */
    private $query;

    /**
     * Fragment.
     * @var string
     */
    private $fragment;

    /**
     * Segments.
     * @var array
     */
    private $segments = [];

    /**
     * Root.
     * @var string
     */
    private $root = '/';

    /**
     * Constructor.
     * @param string $source
     * @param string $root
     */
    public function __construct(string $source = '', string $root = null)
    {
        $this->setSource($source);

        if ($root) {
            $this->setRoot($root);
        }

        // set properties
        $source = parse_url($source);
        if (!empty($source)) {
            isset($source['scheme']) &&
                $this->setScheme($source['scheme']);
            isset($source['host']) &&
                $this->setHost($source['host']);
            isset($source['port']) &&
                $this->setPort((int) $source['port']);
            isset($source['user']) &&
                $this->setUser($source['user']);
            isset($source['pass']) &&
                $this->setPass($source['pass']);
            isset($source['path']) &&
                $this->setPath($source['path']);
            isset($source['query']) &&
                $this->setQuery($source['query']);
            isset($source['fragment']) &&
                $this->setFragment($source['fragment']);

            // segments
            $this->generateSegments();
        }
    }

    /**
     * To string.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set source.
     * @param  string $source
     * @return self
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     * @return string
     */
    final function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set scheme.
     * @param  string $scheme
     * @return self
     */
    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get scheme.
     * @return ?string
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * Set host.
     * @param  string $host
     * @return self
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     * @return ?string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Set port.
     * @param  int port
     * @return self
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     * @return ?int
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Set user.
     * @param  string $user
     * @return self
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     * @return ?string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * Set pass.
     * @param  string $pass
     * @return self
     */
    public function setPass(string $pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get pass.
     * @return ?string
     */
    public function getPass(): ?string
    {
        return $this->pass;
    }

    /**
     * Set path.
     * @param  strin $path
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     * @return ?string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Set query.
     * @param  string $query
     * @return self
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query.
     * @return ?string
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Set fragment.
     * @param  string $fragment
     * @return self
     */
    public function setFragment(string $fragment): self
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Get fragment.
     * @return ?string
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * Set root.
     * @param  string $root
     * @return self
     */
    public function setRoot(string $root): self
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root.
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Is root.
     * @return bool
     */
    public function isRoot(): bool
    {
        return ($this->root == $this->path);
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
     * Segments arguments.
     * @param  int $slice
     * @return array
     */
    public function segmentArguments(int $slice): array
    {
        return array_slice($this->segments, $slice);
    }

    /**
     * To string.
     * @param  array $exclude
     * @return string
     */
    public function toString(array $exclude = []): string
    {
        $array = $this->toArray($exclude);
        $return = '';

        isset($array['scheme']) &&
            $return .= $array['scheme'] . '://';
        if (isset($array['user']) || isset($array['pass'])) {
            isset($array['user']) &&
                $return .= $array['user'];
            isset($array['pass']) &&
                $return .= ':' . $array['pass'];
            $return .= '@';
        }
        isset($array['host']) &&
            $return .= $array['host'];
        isset($array['port']) &&
            $return .= ':' . $array['port'];
        isset($array['path']) &&
            $return .= $array['path'];
        isset($array['query']) &&
            $return .= '?' . $array['query'];
        isset($array['fragment']) &&
            $return .= '#' . $array['fragment'];

        return $return;
    }

    /**
     * To array.
     * @param  array $exclude
     * @return array
     */
    public function toArray(array $exclude = []): array
    {
        $return = [];
        foreach (['scheme', 'host', 'port', 'user',
                  'pass', 'path', 'query', 'fragment'] as $key) {
            if (!in_array($key, $exclude)) {
                $return[$key] = $this->{$key};
            }
        }

        return $return;
    }

    /**
     * Generate segments.
     * @return void
     */
    private function generateSegments(): void
    {
        $path = $this->path;
        if ($path && $path != '/') {
            // remove root
            if ($this->root && $this->root != '/') {
                $path = substr($path, strlen($this->root));
            }

            $this->segments = preg_split('~/+~', $path, -1, PREG_SPLIT_NO_EMPTY);
        }
    }
}