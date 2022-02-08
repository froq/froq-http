<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\request\{Segments, UriException};
use froq\http\Url;

/**
 * Uri.
 *
 * @package froq\http\request
 * @object  froq\http\request\Uri
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Uri extends Url
{
    /** @var froq\http\request\Segments */
    private Segments $segments;

    /** @var array */
    protected static array $components = ['path', 'query', 'queryParams', 'fragment'];

    /**
     * Constructor.
     *
     * @param  array|string $source
     * @throws froq\http\request\UriException
     */
    public function __construct(array|string $source)
    {
        try {
            parent::__construct($source, self::$components);
        } catch (\Throwable $e) {
            throw new UriException($e);
        }
    }

    /**
     * Get a segment.
     *
     * @param  int|string $key
     * @param  mixed|null $default
     * @return mixed|null
     * @throws froq\http\request\UriException
     */
    public function segment(int|string $key, mixed $default = null): mixed
    {
        if (isset($this->segments)) {
            return $this->segments->get($key, $default);
        }

        throw new UriException(
            'Property $segments not set yet [tip: method generateSegments() '.
            'not called yet]'
        );
    }

    /**
     * Get segments property or params.
     *
     * @param  array<int|string>|null $keys
     * @param  array|null             $default
     * @return froq\http\request\Segments|array
     */
    public function segments(array $keys = null, array $default = null): Segments|array
    {
        if (isset($this->segments)) {
            if (!func_num_args()) {
                return $this->segments;
            }

            $ret = [];
            foreach ($keys as $key) {
                $ret[] = $this->segments->get($key, $default);
            }
            return $ret;
        }

        throw new UriException(
            'Property $segments not set yet [tip: method generateSegments() '.
            'not called yet]'
        );
    }

    /**
     * Generate segments.
     *
     * @param  string|null $root
     * @return void
     * @throws froq\http\request\UriException
     * @internal
     */
    public function generateSegments(string $root = null): void
    {
        $path = $this->get('path', '');

        [$path, $segments, $segmentsRoot]
            = [rawurldecode($path), [], Segments::ROOT];

        if ($path != '' && $path != $segmentsRoot) {
            // Drop root if exists.
            if ($root != '' && $root != $segmentsRoot) {
                $root = '/' . trim($root, '/');

                // Prevent wrong generate action.
                if (!str_starts_with($path, $root)) {
                    throw new UriException(
                        'URI path `%s` has no root such `%s`',
                        [$path, $root]
                    );
                }

                // Drop root from path.
                $path = substr($path, strlen($root));

                // Update segments root.
                $segmentsRoot = $root;
            }

            $segments = preg_split('~/+~', $path, flags: 1);

            // In any case.
            if ($segments === false) {
                throw new UriException(
                    'Cannot generate segments [error: %s]',
                    '@error'
                );
            }
        }

        $this->segments = Segments::fromArray($segments, $segmentsRoot);
    }
}
