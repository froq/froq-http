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
use froq\http\Message;
use froq\http\request\{RequestTrait, Method, Scheme, Uri, Client, Params, Files};
use Error;

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
     * Request trait.
     * @see froq\http\request\RequestTrait
     */
    use RequestTrait;

    /**
     * Method.
     * @var froq\http\request\Method
     */
    protected Method $method;

    /**
     * Scheme.
     * @var froq\http\request\Scheme
     */
    protected Scheme $scheme;

    /**
     * Uri.
     * @var froq\http\request\Uri
     */
    protected Uri $uri;

    /**
     * Client.
     * @var froq\http\request\Client
     */
    protected Client $client;

    /**
     * Constructor.
     * @param froq\App
     */
    public function __construct(App $app)
    {
        parent::__construct($app, Message::TYPE_REQUEST);

        $this->method = new Method($_SERVER['REQUEST_METHOD']);
        $this->scheme = new Scheme($_SERVER['REQUEST_SCHEME']);
        $this->uri    = new Uri($_SERVER['REQUEST_URI']);
        $this->client = new Client();

        $headers = $this->loadHeaders();

        // Set/parse body for overriding methods (put, delete etc. or even for get).
        // Note that, 'php://input' is not available with enctype="multipart/form-data".
        // @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input.
        $content = strval(file_get_contents('php://input'));
        $contentType = strtolower($headers['content-type'] ?? '');

        $_GET = $this->loadGlobal('GET');
        if ($content != '' && strpos($contentType, 'multipart/form-data') === false) {
            $_POST = $this->loadGlobal('POST', $content, strpos($contentType, '/json') !== false);
        }
        $_COOKIE = $this->loadGlobal('COOKIE');

        // Fill body object.
        $this->setBody($content, ['type' => $contentType]);

        // Fill & lock headers and cookies objects.
        foreach ($headers as $name => $value) {
            $this->headers->add($name, $value);
        }
        foreach ($_COOKIE as $name => $value) {
            $this->cookies->add($name, $value);
        }
        $this->headers->readOnly(true);
        $this->cookies->readOnly(true);
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
     * Scheme.
     * @return froq\http\request\Scheme
     */
    public function scheme(): Scheme
    {
        return $this->scheme;
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
     * Client.
     * @return froq\http\request\Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Params.
     * @return array
     */
    public function params(): array
    {
        return Params::all();
    }

    /**
     * Files.
     * @return array
     */
    public function files(): array
    {
        return Files::all();
    }

    /**
     * Input.
     * @return string
     * @since  4.5
     */
    public function input(): string
    {
        return (string) file_get_contents('php://input');
    }

    /**
     * Input json.
     * @return array
     * @since  4.5
     */
    public function inputJson(): array
    {
        return (array) json_decode(trim($this->input()), true);
    }

    /**
     * Load headers.
     * @return array
     */
    private function loadHeaders(): array
    {
        try {
            $headers = (array) getallheaders();
        } catch (Error $e) {
            $headers = [];
            foreach ((array) $_SERVER as $key => $value) {
                if (strpos((string) $key, 'HTTP_') === 0) {
                    $headers[str_replace(['_', ' '], '-', substr($key, 5))] = $value;
                }
            }
        }

        // Lowerize keys.
        $headers = array_change_key_case($headers, CASE_LOWER);

        // Content issues.
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['CONTENT_MD5'])) {
            $headers['content-md5'] = $_SERVER['CONTENT_MD5'];
        }

        // Authorization issues.
        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $headers['authorization'] = 'Basic '.
                    base64_encode($_SERVER['PHP_AUTH_USER'] .':'. ($_SERVER['PHP_AUTH_PW'] ?? ''));
            }
        }

        return $headers;
    }

    /**
     * Load global (without changing dotted keys).
     * @param  string $name
     * @param  string $source
     * @param  bool   $sourceJson
     * @return array
     */
    private function loadGlobal(string $name, string $source = '', bool $sourceJson = false): array
    {
        $encode = false;

        switch ($name) {
            case 'GET':
                $source = $_SERVER['QUERY_STRING'] ?? '';
                $encode = true;
                break;
            case 'POST':
                // This is checked in constructor via content-type header.
                if ($sourceJson) {
                    return (array) json_decode(trim($source), true);
                }
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
