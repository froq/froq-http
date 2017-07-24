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
     * @var string|array
     */
    private $body;

    /**
     * Body raw.
     * @var string
     */
    private $bodyRaw;

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
     * Get method.
     * @return Froq\Http\Request\Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * Get uri.
     * @return Froq\Http\Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
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

        $headers = self::loadHttpHeaders();

        // set/parse body for overwrite methods
        switch ($this->method->getName()) {
            case Http::METHOD_PUT:
            case Http::METHOD_POST:
            case Http::METHOD_PATCH:
                $body = (string) file_get_contents('php://input');
                $this->body = $body;
                $this->bodyRaw = $body;

                $contentType = trim($headers['Content-Type'] ?? '');
                // @link https://stackoverflow.com/questions/477816/what-is-the-correct-json-content-type
                if (stripos($contentType, 'application/json') === 0) {
                    $this->body = json_decode($this->body, true);
                } elseif (stripos($contentType, 'application/x-www-form-urlencoded') === 0) {
                    // fix dotted POST keys
                    $this->body = $_POST = $this->loadGlobalVar('POST', $body);
                } else {
                    $this->body = $_POST;
                }
                break;
        }

        // fix dotted COOKIE keys
        $_COOKIE = $this->loadGlobalVar('COOKIE');

        $this->time = (int) $_SERVER['REQUEST_TIME'];
        $this->timeFloat = (float) $_SERVER['REQUEST_TIME_FLOAT'];

        $this->uri = new Uri(sprintf('%s://%s%s',
            $this->scheme, $_SERVER['SERVER_NAME'] , $_SERVER['REQUEST_URI']
        ), $options['root'] ?? null);

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
     * Load HTTP headers.
     * @return array
     */
    final private function loadHttpHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders(); // apache
        } else {
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (stripos(strval($key), 'HTTP_') === 0) {
                    $headers[
                        // normalize key
                        implode('-', array_map('ucwords', explode('_', strtolower(substr($key, 5)))))
                    ] = $value;
                }
            }
        }

        // content-* issues
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['CONTENT_MD5'])) {
            $headers['Content-MD5'] = $_SERVER['CONTENT_MD5'];
        }

        // authorization issues
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $headers['Authorization'] = 'Basic ' .
                    base64_encode($_SERVER['PHP_AUTH_USER'] .':'. ($_SERVER['PHP_AUTH_PW'] ?? ''));
            }
        }

        return $headers;
    }

    /**
     * Load global var (fix dotted param keys).
     *
     * SORRY RASMUS, SORRY ZEEV..
     * @see https://github.com/php/php-src/blob/master/main/php_variables.c#L93
     *
     * @param  string $name
     * @param  string $source
     * @return array
     */
    final private function loadGlobalVar(string $name, string $source = ''): array
    {
        $var = [];

        switch ($name) {
            case 'GET':
                $source = $_SERVER['QUERY_STRING'];
                break;
            case 'POST':
                break;
            case 'COOKIE':
                if (isset($_SERVER['HTTP_COOKIE'])) {
                    $source = implode('&', array_map('trim', explode(';', $_SERVER['HTTP_COOKIE'])));
                }
                break;
        }

        if (empty($source)) {
            return $var;
        }

        // keep keys hexed
        $source = preg_replace_callback('~(^|(?<=&))[^=[&]+~', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $source);

        parse_str($source, $source);

        foreach ($source as $key => $value) {
            $key = hex2bin((string) $key);

            // not array
            if (strpos($key, '[') === false) {
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
