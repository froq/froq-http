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

use Froq\Util\Util;
use Froq\Util\Traits\GetterTrait;
use Froq\Http\Request\{Params, Files, Method};

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Request
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use GetterTrait;

    /**
     * HTTP Version.
     * @var string
     */
    private $httpVersion;

    /**
     * Scheme.
     * @var string
     */
    private $scheme;

    /**
     * Method.
     * @var string
     */
    private $method;

    /**
     * URI.
     * @var string
     */
    private $uri;

    /**
     * Body.
     * @var array
     */
    private $body = [];

    /**
     * Body raw.
     * @var string
     */
    private $bodyRaw = ''; // @wait

    /**
     * Time.
     * @var int/float
     */
    private $time;

    /**
     * Request time float.
     * @var int
     */
    private $timeFloat;

    /**
     * Client.
     * @var Froq\Http\Client
     */
    private $client;

    /**
     * Headers.
     * @var Froq\Http\Headers
     */
    private $headers;

    /**
     * Cookies.
     * @var Froq\Http\Cookies
     */
    private $cookies;

    /**
     * Params.
     * @var Froq\Http\Request\Params
     */
    private $params;

    /**
     * Files.
     * @var Froq\Http\Request\Files
     */
    private $files;

    /**
     * Constructor.
     */
    final public function __construct()
    {
        $this->httpVersion = Http::detectVersion();
    }

    /**
     * Init.
     * @param  array $options
     * @return self
     */
    final public function init(array $options = []): self
    {
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $this->scheme = strtolower($_SERVER['REQUEST_SCHEME']);
        } elseif ($_SERVER['SERVER_PORT'] == '443') {
            $this->scheme = 'https';
        } else {
            $this->scheme = 'http';
        }

        $this->method = new Method($_SERVER['REQUEST_METHOD']);

        // fix dotted GET keys
        $_GET = $this->loadGlobalVar('GET');

        // set/parse body for overwrite methods
        switch ($this->method->getName()) {
            case Http::METHOD_PUT:
            case Http::METHOD_POST:
            case Http::METHOD_PATCH:
                // act as post
                $_POST = $this->loadGlobalVar('POST');
                $this->body = $_POST;
                break;
        }

        // fix dotted COOKIE keys
        $_COOKIE = $this->loadGlobalVar('COOKIE');

        $this->time = (int) $_SERVER['REQUEST_TIME'];
        $this->timeFloat = (float) $_SERVER['REQUEST_TIME_FLOAT'];

        $headers = [];
        foreach (getallheaders() as $key => $value) {
            $headers[to_snake_from_dash($key, true)] = $value;
        }

        $this->uri = new Uri(sprintf('%s://%s%s',
            $this->scheme, $_SERVER['SERVER_NAME'] , $_SERVER['REQUEST_URI']
        ), $options['uriRoot'] ?? null);

        $this->client  = new Client();
        $this->headers = new Headers($headers);
        $this->cookies = new Cookies($_COOKIE);
        $this->params  = new Params();
        $this->files   = new Files($_FILES);

        return $this;
    }

    /**
     * Get param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function getParam(string $name, $valueDefault = null)
    {
        return $this->params->get->get($name, $valueDefault);
    }

    /**
     * Get params.
     * @return array
     */
    final public function getParams(): array
    {
        return $this->params->get->toArray();
    }

    /**
     * Post param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function postParam(string $name, $valueDefault = null)
    {
        return $this->params->post->get($name, $valueDefault);
    }

    /**
     * Post params.
     * @return array
     */
    final public function postParams(): array
    {
        return $this->params->post->toArray();
    }

    /**
     * Cookie param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function cookieParam(string $name, $valueDefault = null)
    {
        return $this->params->cookie->get($name, $valueDefault);
    }

    /**
     * Cookie params.
     * @return array
     */
    final public function cookieParams(): array
    {
        return $this->params->cookie->toArray();
    }

    /**
     * Load global var (fix dotted param keys).
     *
     * SORRY RASMUS, SORRY ZEEV..
     * @see https://github.com/php/php-src/blob/master/main/php_variables.c#L93
     *
     * @param  string $name
     * @return array
     */
    final private function loadGlobalVar(string $name): array
    {
        $src = '';
        $var = [];

        switch ($name) {
            case 'GET':
                $src = $_SERVER['QUERY_STRING'];
                break;
            case 'POST':
                $src = file_get_contents('php://input');
                break;
            case 'COOKIE':
                if (isset($_SERVER['HTTP_COOKIE'])) {
                    $src = implode('&', array_map('trim', explode(';', $_SERVER['HTTP_COOKIE'])));
                }
                break;
        }

        if (empty($src)) {
            return $var;
        }

        // keep keys hexed
        $src = preg_replace_callback('~(^|(?<=&))[^=[&]+~', function($m) {
            return bin2hex(urldecode($m[0]));
        }, $src);

        parse_str($src, $src);

        foreach ($src as $key => $value) {
            $key = hex2bin((string) $key);

            // not array
            if (false === strpos($key, '[')) {
                $var[$key] = $value;
            } else {
                // handle array
                parse_str("{$key}={$value}", $value);

                $var = array_merge_recursive($var, $value);
            }
        }

        return $var;
    }
}
