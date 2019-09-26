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

use froq\App;
use froq\util\Util;
use froq\http\request\{Method, Uri, Client, Params, Files};
use froq\http\response\Body;

/**
 * Request.
 * @package froq\http
 * @object  froq\http\Request
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Request extends Message
{
    /**
     * Method.
     * @var froq\http\request\Method
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
     * @var int
     */
    private $time;

    /**
     * Request time float.
     * @var float
     */
    private $timeFloat;

    /**
     * Client.
     * @var froq\http\request\Client
     */
    private $client;

    /**
     * Params.
     * @var froq\http\request\Params
     */
    private $params;

    /**
     * Files.
     * @var froq\http\request\Files
     */
    private $files;

    /**
     * Constructor.
     * @param froq\App
     */
    public function __construct(App $app)
    {
        parent::__construct($app, parent::TYPE_REQUEST);

        $this->method = new Method($_SERVER['REQUEST_METHOD']);

        $this->uri = new Uri(sprintf('%s://%s%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_URI']));
        $this->uri->generateSegments($this->app->root());

        // fix dotted GET keys
        $_GET = $this->loadGlobalVar('GET');

        $headers = $this->loadHttpHeaders();
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }

        // set/parse body for override methods
        $body = (string) file_get_contents('php://input');
        $this->body = $body;
        $this->bodyRaw = $body;

        if (stripos(trim($headers['Content-Type'] ?? ''), 'application/x-www-form-urlencoded') === 0) {
            // fix dotted POST keys
            $this->body = $_POST = $this->loadGlobalVar('POST', $body);
        } else {
            $this->body = $_POST;
        }

        // fix dotted COOKIE keys
        $cookies = $this->loadGlobalVar('COOKIE');
        foreach ($cookies as $name => $value) {
            $this->cookies[$name] = $value;
        }
        $_COOKIE = $cookies;

        $this->time = (int) $_SERVER['REQUEST_TIME'];
        $this->timeFloat = (float) $_SERVER['REQUEST_TIME_FLOAT'];

        $this->client = new Client();
        $this->params = new Params();
        $this->files = new Files($_FILES);
    }

    /**
     * Method.
     * @return froq\http\request\Method
     */
    public function method(): Method
    {
        return $this->method;
    }

    /**
     * Uri.
     * @return froq\http\request\Uri
     */
    public function uri(): Uri
    {
        return $this->uri;
    }

    /**
     * Get body.
     * @return ?array
     */
    public function getBody(): ?array
    {
        return $this->body;
    }

    /**
     * Get body raw.
     * @return ?string
     */
    public function getBodyRaw(): ?string
    {
        return $this->bodyRaw;
    }

    /**
     * Get time.
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * Get time float.
     * @return float
     */
    public function getTimeFloat(): float
    {
        return $this->timeFloat;
    }

    /**
     * Client.
     * @return froq\http\request\Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Params.
     * @return froq\http\request\Params
     */
    public function params(): Params
    {
        return $this->params;
    }

    /**
     * Files.
     * @return froq\http\request\Files
     */
    public function files(): Files
    {
        return $this->files;
    }

    /**
     * Get param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function getParam(string $name, $valueDefault = null)
    {
        return $this->params->get($name, $valueDefault);
    }

    /**
     * Get params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function getParams(array $names = null, $valuesDefault = null): array
    {
        return $this->params->gets($names, $valuesDefault);
    }

    /**
     * Post param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function postParam(string $name, $valueDefault = null)
    {
        return $this->params->post($name, $valueDefault);
    }

    /**
     * Post params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function postParams(array $names = null, $valuesDefault = null): array
    {
        return $this->params->posts($names, $valuesDefault);
    }

    /**
     * Cookie param.
     * @param  string $name
     * @param  any    $valueDefault
     * @return any
     */
    public function cookieParam(string $name, $valueDefault = null)
    {
        return $this->params->cookie($name, $valueDefault);
    }

    /**
     * Cookie params.
     * @param  array|null $names
     * @param  any        $valuesDefault
     * @return array
     */
    public function cookieParams(array $names = null, $valuesDefault = null): array
    {
        return $this->params->cookies($names, $valuesDefault);
    }

    /**
     * Load http headers.
     * @return array
     */
    private function loadHttpHeaders(): array
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

        // content issues
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
                $headers['Authorization'] = 'Basic '.
                    base64_encode($_SERVER['PHP_AUTH_USER'] .':'. ($_SERVER['PHP_AUTH_PW'] ?? ''));
            }
        }

        return $headers;
    }

    /**
     * Load global var (without changing dotted param keys).
     * @param  string $name
     * @param  string $source
     * @return array
     */
    private function loadGlobalVar(string $name, string $source = ''): array
    {
        $encode = false;

        switch ($name) {
            case 'GET':
                $source = $_SERVER['QUERY_STRING'] ?? '';
                $encode = true;
                break;
            case 'POST': // pass
                break;
            case 'COOKIE':
                if (isset($_SERVER['HTTP_COOKIE'])) {
                    $source = implode('&', array_map('trim', explode(';', $_SERVER['HTTP_COOKIE'])));
                }
                break;
        }

        return Util::parseQueryString($source, $encode);
    }
}
