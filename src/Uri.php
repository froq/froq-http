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

namespace Froq\Http;

use Froq\Util\Traits\GetterTrait;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Uri
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Uri
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use GetterTrait;

    /**
     * Source.
     * @var string
     */
    private $source;

    /**
     * Scheme
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
     * Segments root.
     * @var array
     */
    private $segmentsRoot;

    /**
     * Constructor.
     * @param string $source
     */
    final public function __construct(string $source = '')
    {
        $this->setSource($source);

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
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Set source.
     * @param  string $source
     * @return self
     */
    final public function setSource(string $source): self
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
    final public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get scheme.
     * @return string|null
     */
    final public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set host.
     * @param  string|null $host
     * @return self
     */
    final public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     * @return string|null
     */
    final public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port.
     * @param  int|null port
     * @return self
     */
    final public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port.
     * @return int|null
     */
    final public function getPort()
    {
        return $this->port;
    }

    /**
     * Set user.
     * @param  string $user
     * @return self
     */
    final public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     * @return string|null
     */
    final public function getUser()
    {
        return $this->user;
    }

    /**
     * Set pass.
     * @param  string|null $pass
     * @return self
     */
    final public function setPass(string $pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get pass.
     * @return string|null
     */
    final public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set path.
     * @param  string|null $path
     * @return self
     */
    final public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     * @return string|null
     */
    final public function getPath()
    {
        return $this->path;
    }

    /**
     * Set query.
     * @param  string|null $query
     * @return self
     */
    final public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query.
     * @return string|null
     */
    final public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set fragment.
     * @param  string|null $fragment
     * @return self
     */
    final public function setFragment(string $fragment): self
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Get fragment.
     * @return string|null
     */
    final public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Is root.
     * @return bool
     */
    final public function isRoot(): bool
    {
        return ($this->path == '/');
    }

    /**
     * Segment.
     * @param  int $i
     * @param  any $default
     * @return any
     */
    final public function segment(int $i, $default = null)
    {
        return $this->segments[$i] ?? $default;
    }

    /**
     * Segments.
     * @return array
     */
    final public function segments(): array
    {
        return $this->segments;
    }

    /**
     * To string.
     * @param  array $exclude
     * @return string
     */
    final public function toString(array $exclude = []): string
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
    final public function toArray(array $exclude = []): array
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
     * Set segments root.
     * @param  string $segmentsRoot
     * @return self
     */
    final public function setSegmentsRoot(string $segmentsRoot): self
    {
        $this->segmentsRoot = $segmentsRoot;

        return $this;
    }

    /**
     * Get segments root.
     * @return string
     */
    final public function getSegmentsRoot(): string
    {
        return $this->segmentsRoot;
    }

    /**
     * Generate segments.
     * @return void
     */
    final public function generateSegments()
    {
        $path = $this->path;
        if ($path != '' && $path != '/') {
            // remove root
            if ($this->segmentsRoot != '' && $this->segmentsRoot != '/') {
                $path = preg_replace('~^'. preg_quote($this->segmentsRoot) .'~', '', $path);
            }

            $this->segments = array_filter(array_map('trim',
                preg_split('~/+~', $path, -1, PREG_SPLIT_NO_EMPTY)));
        }
    }
}
