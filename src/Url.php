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
    /** @var array|string|null */
    protected array|string|null $source;

    /** @var array<string> */
    protected static array $components = ['scheme', 'host', 'port', 'user', 'pass', 'path',
        'query', 'queryParams', 'fragment', 'origin', 'authority'];

    /**
     * Constructor.
     *
     * @param   array<string>|string|null source
     * @param   array<string>|null        $components
     * @@throws froq\http\UrlException
     */
    public function __construct(array|string $source = null, array $components = null)
    {
        parent::__construct($components ??= self::$components);

        $this->source = $source;

        if ($source === null) {
            return;
        }

        if (is_string($source)) {
            if ($source == '') {
                throw new UrlException('Invalid URL/URI source, empty source given');
            }

            $source = http_parse_url($source);
            if (!$source) {
                throw new UrlException('Invalid URL/URI source, parsing failed');
            }
        } else {
            // Update query stuff.
            if (isset($source['query']) || isset($source['queryParams'])) {
                $temp = [];
                if (isset($source['query'])) {
                    $temp = http_query_parse($source['query']);
                }
                if (isset($source['queryParams'])) {
                    $temp = array_replace($temp, $source['queryParams']);
                }
                $source['query'] = http_query_build($temp);
                $source['queryParams'] = $temp;
            }
        }

        if (!isset($source['origin'])) {
            $origin = null;
            if (isset($source['scheme'], $source['host'])) {
                $origin = sprintf('%s://%s%s', $source['scheme'], $source['host'], (
                    isset($source['port']) ? ':' . $source['port'] : ''
                ));
            }

            $source['origin'] = $origin;
        }

        if (!isset($source['authority'])) {
            $authority = null;
            isset($source['user']) && $authority .= $source['user'];
            isset($source['pass']) && $authority .= ':' . $source['pass'];

            // Add separator.
            if ($authority != '') {
                $authority .= '@';
            }

            isset($source['host']) && $authority .= $source['host'];
            isset($source['port']) && $authority .= ':' . $source['port'];

            $source['authority'] = $authority;
        }

        // Use self component names only.
        $source = array_include($source, $components);

        foreach ($source as $name => $value) {
            $this->set($name, $value);
        }
    }

    /** @magic */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Get source property.
     *
     * @return array|string|null
     */
    public function source(): array|string|null
    {
        return $this->source;
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        return http_build_url($this->data);
    }
}
