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
 * A URL object with strict/optional components that accessible via set/get methods or via
 * `__call()` magic with their names (eg: `getPath()`), and some other utility methods.
 *
 * @package froq\http
 * @object  froq\http\Url
 * @author  Kerem Güneş
 * @since   4.0
 */
class Url extends ComponentCollection implements Stringable
{
    /** @var array<string>|string */
    protected array|string $source;

    /** @var array<string> */
    protected static array $components = ['scheme', 'host', 'port', 'user', 'pass', 'path',
        'query', 'queryParams', 'fragment', 'authority', 'userInfo'];

    /**
     * Constructor.
     *
     * @param   array<string>|string|null source
     * @param   array<string>|null        $components
     * @@throws froq\http\UrlException
     */
    public function __construct(array|string $source = null, array $components = null)
    {
        $components ??= self::$components;

        // Set components.
        parent::__construct($components);

        if ($source === null) {
            return;
        }

        // Keep source.
        $this->source = $source;

        if (is_string($source)) {
            $startsWithSlashes = str_starts_with($source, '//');

            // Fix beginning-slashes issue falsifying parse_url();
            if ($startsWithSlashes) {
                $source = '/' . ltrim($source, '/');
            }

            $source = parse_url($source);
            if ($source === false) {
                throw new UrlException('Invalid URL/URI source, parsing failed');
            }

            // Put slashes back (to keep source original).
            if ($startsWithSlashes) {
                $source['path'] = '/' . $source['path'];
            }
        }

        if (isset($source['query'])) {
            $query = Arrays::pull($source, 'query');
            if ($query != '') {
                $source += ['query' => $query,
                            'queryParams' => Util::parseQueryString($query)];
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

                // Add separator.
                $authority .= '@';
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
        [$scheme, $authority, $path, $query, $queryParams, $fragment] = array_select(
            $this->data, ['scheme', 'authority', 'path', 'query', 'queryParams', 'fragment']
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

        if ($path != '')     $ret .= $path;
        if ($query != '')    $ret .= '?' . $query;
        if ($fragment != '') $ret .= '#' . $fragment;

        return $ret;
    }
}
