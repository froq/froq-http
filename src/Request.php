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
use Froq\Util\Traits\GetterTrait as Getter;
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
    use Getter;

    /**
     * HTTP Version.
     * @var string
     */
    private $httpVersion;

    /**
     * Request scheme.
     * @var string
     */
    private $scheme;

    /**
     * Request method.
     * @var string
     */
    private $method;

    /**
     * Request URI.
     * @var string
     */
    private $uri;

    /**
     * Parsed body data.
     * @var array
     */
    private $body = [];

    /**
     * Raw body data.
     * @var string
     */
    private $bodyRaw = ''; // @wait

    /**
     * Request time/time float.
     * @var int/float
     */
    private $time;

    /**
     * Request time.
     * @var int
     */
    private $timeFloat;

    /**
     * Client object.
     * @var Froq\Http\Client
     */
    private $client;

    /**
     * Header stack.
     * @var Froq\Http\Headers
     */
    private $headers;

    /**
     * Cookie stack.
     * @var Froq\Http\Cookies
     */
    private $cookies;

    /**
     * Params object.
     * @var Froq\Http\Request\Params
     */
    private $params;

    /**
     * Files object.
     * @var Froq\Http\Request\Files
     */
    private $files;

    /**
     * Constructor.
     */
    final public function __construct()
    {
        // set http version
        $this->httpVersion = Http::detectVersion();
    }

    /**
     * Init.
     * @param  array $options
     * @return self
     */
    final public function init(array $options = []): self
    {
        // set scheme
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $this->scheme = strtolower($_SERVER['REQUEST_SCHEME']);
        } elseif ($_SERVER['SERVER_PORT'] == '443') {
            $this->scheme = 'https';
        } else {
            $this->scheme = 'http';
        }

        // set method
        $this->method = new Method($_SERVER['REQUEST_METHOD']);

        // set uri
        $uri = sprintf('%s://%s%s',
            $this->scheme, $_SERVER['SERVER_NAME'] , $_SERVER['REQUEST_URI']);
        $uriRoot = $options['uriRoot'] ?? '';
        $this->uri = new Uri($uri);
        $this->uri->setSegmentsRoot($uriRoot)->generateSegments();

        // fix dotted get keys
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

        // fix dotted cookie keys
        $_COOKIE = $this->loadGlobalVar('COOKIE');

        // set times
        $this->time = (int) $_SERVER['REQUEST_TIME'];
        $this->timeFloat = (float) $_SERVER['REQUEST_TIME_FLOAT'];

        // set client that contains ip & language etc.
        $this->client = new Client();

        $headers = [];
        foreach (getallheaders() as $key => $value) {
            $headers[to_snake_from_dash($key, true)] = $value;
        }

        // set headers/cookies as an object that iterable/traversable
        $this->headers = new Headers($headers);
        $this->cookies = new Cookies($_COOKIE);

        // set params
        $this->params = new Params();

        // set files
        $this->files = new Files($_FILES);

        return $this;
    }

    /**
     * Get a GET param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function getParam(string $name, $valueDefault = null)
    {
        return $this->params->get->get($name, $valueDefault);
    }

    /**
     * Get all GET params.
     * @param  bool $setNoneNull
     * @return array
     */
    final public function getParams(bool $setNoneNull = false): array
    {
        return $this->params->get->toArray($setNoneNull);
    }

    /**
     * Get a POST param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function postParam(string $name, $valueDefault = null)
    {
        return $this->params->post->get($name, $valueDefault);
    }

    /**
     * Get all POST params.
     * @param  bool $setNoneNull
     * @return array
     */
    final public function postParams(bool $setNoneNull = false): array
    {
        return $this->params->post->toArray($setNoneNull);
    }

    /**
     * Get all COOKIE params.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    final public function cookieParam(string $name, $valueDefault = null)
    {
        return $this->params->cookie->get($name, $valueDefault);
    }

    /**
     * Get all COOKIE params.
     * @param  bool $setNoneNull
     * @return array
     */
    final public function cookieParams(bool $setNoneNull = false): array
    {
        return $this->params->cookie->toArray($setNoneNull);
    }

    /**
     * Fix dotted param keys.
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

        // no var source?
        if (empty($src)) {
            return $var;
        }

        // hex keys
        $src = preg_replace_callback('~(^|(?<=&))[^=[&]+~', function($m) {
            return bin2hex(urldecode($m[0]));
        }, $src);

        // parse
        parse_str($src, $src);

        foreach ($src as $key => $value) {
            $key = hex2bin((string) $key);

            // not array
            if (strpos($key, '[') === false) {
                $var[$key] = $value;
            } else {
                // handle arrays
                parse_str("{$key}={$value}", $value);

                $var = array_merge_recursive($var, $value);
            }
        }

        return $var;
    }
}
