<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\http\request\{Segments, UriException};
use froq\http\Url;
use Throwable;

/**
 * Uri.
 *
 * @package froq\http\request
 * @object  froq\http\request\Uri
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Uri extends Url
{
    /** @var froq\http\request\Segments */
    private Segments $segments;

    /**
     * Constructor.
     * @param  array|string $source
     * @throws froq\http\request\UriException
     */
    public function __construct(array|string $source)
    {
        try {
            parent::__construct($source, ['path', 'query', 'queryParams', 'fragment']);
        } catch (Throwable $e) {
            throw new UriException($e);
        }

        $this->readOnly(true); // Lock.
    }

    /**
     * Get a segment.
     *
     * @param  int|string $key
     * @param  any|null   $default
     * @return any|null
     * @throws froq\http\request\UriException
     */
    public function segment(int|string $key, $default = null)
    {
        if (isset($this->segments)) {
            return $this->segments->get($key, $default);
        }

        throw new UriException('Property $segments not set yet [tip: method generateSegments()'
            . ' not called yet]');
    }

    /**
     * Get segments.
     *
     * @param  array|null $keys
     * @param  any|null   $default
     * @return froq\http\request\Segments|array
     */
    public function segments(array $keys = null, $default = null): Segments|array
    {
        if (isset($this->segments)) {
            if ($keys === null) {
                return $this->segments;
            }

            $ret = [];
            foreach ($keys as $key) {
                $ret[] = $this->segments->get($key, $default);
            }
            return $ret;
        }

        throw new UriException('Property $segments not set yet [tip: method generateSegments()'
            . ' not called yet]');
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
        $path = $this->get('path') ?: '';

        [$path, $segments, $segmentsRoot] = [
            rawurldecode($path), [], Segments::ROOT
        ];

        if ($path && $path != $segmentsRoot) {
            // Drop root if exists.
            if ($root && $root != $segmentsRoot) {
                $root = '/'. trim($root, '/');

                // Prevent wrong generate action.
                if (!str_starts_with($path, $root)) {
                    throw new UriException('URI path `%s` has no root such `%s`', [$path, $root]);
                }

                // Drop root from path.
                $path = substr($path, strlen($root));

                // Update segments root.
                $segmentsRoot = $root;
            }

            $segments = (array) preg_split('~/+~', $path, -1, 1);
        }

        $this->segments = Segments::fromArray($segments, $segmentsRoot);
    }
}
