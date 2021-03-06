<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\http\UrlException;
use froq\collection\ComponentCollection;
use froq\common\interface\Stringable;
use froq\util\{Util, Arrays};

/**
 * Url.
 *
 * Represents a URL object with strict/optional components that accessible via set/get methods or via
 * `__call()` magic with their names (eg: `getScheme()`), and some other utility methods.
 *
 * @package froq\http
 * @object  froq\http\Url
 * @author  Kerem Güneş
 * @since   4.0
 */
class Url extends ComponentCollection implements Stringable
{
    /** @var array|string */
    protected array|string $source;

    /** @var array */
    protected static array $components = ['scheme', 'host', 'port', 'user', 'pass', 'path',
        'query', 'queryParams', 'fragment', 'authority', 'userInfo'];

    /**
     * Constructor.
     *
     * @param   array|string|null $source
     * @param   array|null        $components
     * @@throws froq\http\UrlException
     */
    public function __construct(array|string $source = null, array $components = null)
    {
        $components ??= self::$components;

        // Set components.
        parent::__construct($components);

        if ($source == null) {
            return;
        }

        // Keep source.
        $this->source = $source;

        if (is_string($source)) {
            $i = 0;
            // $colon = strpos($source, ':');

            // Fix beginning-slashes & colons issue that falsifying parse_url();
            if (str_starts_with($source, '//')) {
                while (($source[++$i] ?? '') === '/');

                $source = '/'. substr($source, $i);
            }

            // if ($colon) {
            //     $source = str_replace(':', '%3A', $source);
            // }

            $source = parse_url($source);
            if ($source === false) {
                throw new UrlException('Invalid URL/URI source, parsing failed');
            }

            // Put slashes & colons back (to keep source original).
            if (isset($source['path'])) {
                if ($i) {
                    $source['path'] = str_repeat('/', $i - 1) . $source['path'];
                }

                // if ($colon) {
                //     $source['path'] = str_replace('%3A', ':', $source['path']);
                // }
            }
        }

        if (isset($source['query'])) {
            $query = Arrays::pull($source, 'query');
            if ($query != null) {
                $source += ['query' => $query, 'queryParams' => Util::parseQueryString($query)];
            }
        }

        if (isset($source['authority'])) {
            $authority = parse_url('scheme://' . $source['authority']);
            if ($authority === false) {
                throw new UrlException('Invalid authority, parsing failed');
            }

            // Drop used fake scheme above.
            unset($authority['scheme']);

            $source = array_merge($source, $authority);
        } else {
            $authority = $userInfo = '';

            isset($source['user']) && $authority .= $source['user'];
            isset($source['pass']) && $authority .= ':' . $source['pass'];

            $userInfo = $authority;

            if ($userInfo != '') {
                $source['userInfo'] = $userInfo;

                $authority .= '@'; // Separate.
            }

            isset($source['host']) && $authority .= $source['host'];
            isset($source['port']) && $authority .= ':' . $source['port'];

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
     * Get source property.
     *
     * @return array|string|null
     */
    public function source(): array|string|null
    {
        return $this->source ?? null;
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        $url = $this->toArray();
        [$scheme, $authority, $path, $query, $queryParams, $fragment] = array_select(
            $url, ['scheme', 'authority', 'path', 'query', 'queryParams', 'fragment']
        );

        $ret = '';

        // Syntax Components: https://tools.ietf.org/html/rfc3986#section-3
        if ($scheme) {
            $ret .= $scheme;
            $ret .= $authority ? '://' . $authority : ':';
        } elseif ($authority) {
            $ret .= $authority;
        }

        if ($queryParams) {
            $query = Util::buildQueryString($queryParams);
        }

        $path     && $ret .= $path;
        $query    && $ret .= '?' . $query;
        $fragment && $ret .= '#' . $fragment;

        return $ret;
    }
}
