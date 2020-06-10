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

namespace froq\http;

use froq\collection\ComponentCollection;
use froq\common\interfaces\Stringable;
use froq\util\{Util, Arrays};
use froq\http\UrlException;

/**
 * Url.
 *
 * Respresents a URL object with strict/optional components that accessible via set/get methods or
 * via `__call()` magic with their names (eg: `getScheme()`), and some other utility methods.
 *
 * @package froq\http
 * @object  froq\http\Url
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 */
class Url extends ComponentCollection implements Stringable
{
    /**
     * Source.
     * @var array|string
     */
    protected $source;

    /**
     * Components.
     * @var array
     */
    protected static array $components = ['scheme', 'host', 'port', 'user', 'pass', 'path',
        'query', 'queryParams', 'fragment', 'authority', 'userInfo'];

    /**
     * Constructor.
     * @param   array|string $source
     * @param   array|null   $components
     * @@throws froq\http\UrlException
     */
    public function __construct($source = null, array $components = null)
    {
        if ($source && !is_array($source) && !is_string($source)) {
            throw new UrlException('Invalide source type "%s" given, valids are: string, array',
                [gettype($source)]);
        }

        $components = $components ?: self::$components;

        // Set components.
        parent::__construct($components);

        // Keep source.
        $this->source = $source;

        if (!$source) {
            return;
        }

        if (is_string($source)) {
            $i = 0;
            $colon = strpos($source, ':');

            // Fix beginning-slashes & colons issue that falsifying parse_url();
            if (strpos($source, '//') === 0) {
                while (($source[++$i] ?? '') === '/');

                $source = '/'. substr($source, $i);
            }
            if ($colon) {
                $source = str_replace(':', '%3A', $source);
            }

            $source = parse_url($source);
            if ($source === false) {
                throw new UrlException('Invalid URL/URI source, parsing failed');
            }

            // Put slashes & colons back (to keep source original).
            if (isset($source['path'])) {
                if ($i) {
                    $source['path'] = str_repeat('/', $i - 1) . $source['path'];
                }
                if ($colon) {
                    $source['path'] = str_replace('%3A', ':', $source['path']);
                }
            }
        }

        if (isset($source['query'])) {
            $query = Arrays::pull($source, 'query');
            if ($query != null) {
                $source += ['query' => $query,
                            'queryParams' => Util::parseQueryString($query)];
            }
        }

        if (isset($source['authority'])) {
            $authority = parse_url('scheme://'. $source['authority']);
            if ($authority === false) {
                throw new UrlException('Invalid authority, parsing failed');
            }

            // Drop used fake scheme above.
            unset($authority['scheme']);

            $source = array_merge($source, $authority);
        } else {
            $authority = $userInfo = '';

            isset($source['user']) && $authority .= $source['user'];
            isset($source['pass']) && $authority .= ':'. $source['pass'];

            $userInfo = $authority;

            if ($userInfo != '') {
                $source['userInfo'] = $userInfo;

                $authority .= '@'; // Separate.
            }

            isset($source['host']) && $authority .= $source['host'];
            isset($source['port']) && $authority .= ':'. $source['port'];

            if ($authority != '') {
                $source['authority'] = $authority;
            }
        }

        // Use self component names only.
        $source = Arrays::include($source, $components);

        foreach ($source as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Get source.
     * @return array|string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @inheritDoc froq\common\interfaces\Stringable
     */
    public function toString(): string
    {
        @ ['scheme' => $scheme, 'authority'   => $authority,   'path'      => $path,
           'query'  => $query,  'queryParams' => $queryParams, 'fragment'  => $fragment
          ] = $this->toArray();

        $ret = '';

        // Syntax Components: https://tools.ietf.org/html/rfc3986#section-3
        if ($scheme) {
            $ret .= $scheme;
            $ret .= $authority ? '://'. $authority : ':';
        } elseif ($authority) {
            $ret .= $authority;
        }

        if ($queryParams) {
            $query = Util::buildQueryString($queryParams);
        }

        $path     && $ret .= $path;
        $query    && $ret .= '?'. $query;
        $fragment && $ret .= '#'. $fragment;

        return $ret;
    }
}
