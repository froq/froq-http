<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http;

use froq\http\request\{RequestTrait, Method, Scheme, Uri, Client, Params, Files, Segments};
use froq\http\{Message, UrlQuery};
use froq\{App, util\Util};
use Error;

/**
 * Request.
 *
 * Represents a HTTP request entity which extends `Message` class and mainly deals with Froq! application
 * and controllers.
 *
 * @package froq\http
 * @object  froq\http\Request
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Request extends Message
{
    /** @see froq\http\request\RequestTrait */
    use RequestTrait;

    /** @var froq\http\request\Method */
    protected Method $method;

    /** @var froq\http\request\Scheme */
    protected Scheme $scheme;

    /** @var froq\http\request\Uri */
    protected Uri $uri;

    /** @var froq\http\request\Client */
    protected Client $client;

    /** @var froq\http\UrlQuery @since 5.1 */
    protected UrlQuery $query;

    /** @var string @since 4.6 */
    private string $id;

    /** @var array *@since 4.6 */
    private array $times;

    /**
     * Constructor.
     *
     * @param froq\App
     */
    public function __construct(App $app)
    {
        parent::__construct($app, Message::TYPE_REQUEST);

        $this->method = new Method($_SERVER['REQUEST_METHOD']);
        $this->scheme = new Scheme($_SERVER['REQUEST_SCHEME']);
        $this->uri    = new Uri($_SERVER['REQUEST_URI']);
        $this->client = new Client();
        $this->id     = get_request_id(); // From util.sugars.
        $this->times  = [$_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME_FLOAT']];

        $headers = $this->prepareHeaders();

        // Set/parse body for overriding methods (put, delete etc. or even for get).
        // Note that, 'php://input' is not available with enctype="multipart/form-data".
        // @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input.
        $content     = $this->input();
        $contentType = strtolower($headers['content-type'] ?? '');

        $_GET = $this->prepareGlobalVariable('GET');

        // Post data always parsed, for GET requests as well (to utilize JSON payloads, thanks ElasticSearch..).
        if ($content != '' && !str_contains($contentType, 'multipart/form-data')) {
            $_POST = $this->prepareGlobalVariable('POST', $content, !!str_contains($contentType, '/json'));
        }

        $_COOKIE = $this->prepareGlobalVariable('COOKIE');

        // Fill body object.
        $this->setBody($content, ($contentType ? ['type' => $contentType] : null));

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
     * Get method property.
     *
     * @return froq\http\request\Method
     */
    public function method(): Method
    {
        return $this->method;
    }

    /**
     * Get scheme property.
     *
     * @return froq\http\request\Scheme
     */
    public function scheme(): Scheme
    {
        return $this->scheme;
    }

    /**
     * Get uri property.
     *
     * @return froq\http\request\Uri
     */
    public function uri(): Uri
    {
        return $this->uri;
    }

    /**
     * Get client property.
     *
     * @return froq\http\request\Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Get query property or create newly.
     *
     * @return froq\http\UrlQuery
     * @since  5.1
     */
    public function query(): UrlQuery
    {
        // More memory friendly..
        return $this->query ??= new UrlQuery($_GET);
    }

    /**
     * Get id property.
     *
     * @return string
     * @since  4.6
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get times property.
     *
     * @return array
     * @since  4.6
     */
    public function times(): array
    {
        return $this->times;
    }

    /**
     * Get all params as GPC sort.
     *
     * @return array
     */
    public function params(): array
    {
        return Params::all();
    }

    /**
     * Get all uploaded files.
     *
     * @return array
     */
    public function files(): array
    {
        return Files::all();
    }

    /**
     * Get PHP input.
     *
     * @return string
     * @since  4.5
     */
    public function input(): string
    {
        return (string) file_get_contents('php://input');
    }

    /**
     * Get PHP input as JSON array.
     *
     * @return array
     * @since  4.5
     */
    public function json(): array
    {
        return (array) json_decode($this->input(), flags: JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING);
    }

    /**
     * Get method name.
     *
     * @return string
     * @since  4.7
     */
    public function getMethod(): string
    {
        return $this->method->getName();
    }

    /**
     * Get scheme name.
     *
     * @return string
     * @since  4.7
     */
    public function getScheme(): string
    {
        return $this->scheme->getName();
    }

    /**
     * Get URI.
     *
     * @param  bool $escape
     * @return string
     * @since  4.7
     */
    public function getUri(bool $escape = false): string
    {
        return !$escape ? $this->uri->toString() : htmlspecialchars($this->uri->toString());
    }

    /**
     * Get URL.
     *
     * @param  bool $escape
     * @return string
     * @since  5.0
     */
    public function getUrl(bool $escape = false): string
    {
        return $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['SERVER_NAME'] . $this->getUri($escape);
    }

    /**
     * Get context, aka URI path.
     *
     * @param  bool $escape
     * @return string
     * @since  4.8
     */
    public function getContext(bool $escape = false): string
    {
        return !$escape ? $this->uri->get('path') : htmlspecialchars($this->uri->get('path'));
    }

    /**
     * Get a URI segment.
     *
     * @param  int|string $key
     * @param  any|null   $default
     * @return any|null
     * @since  5.0
     */
    public function getSegment(int|string $key, $default = null)
    {
        return $this->uri->segment($key, $default);
    }

    /**
     * Get all/many URI segments.
     *
     * @param  array<int|string>|null $keys
     * @param  any|null               $default
     * @return froq\http\request\Segments|array
     * @since  5.0
     */
    public function getSegments(array $keys = null, $default = null): Segments|array
    {
        return $this->uri->segments($keys, $default);
    }

    /**
     * Prepare headers.
     *
     * @return array
     */
    private function prepareHeaders(): array
    {
        try {
            $headers = (array) getallheaders();
        } catch (Error) {
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with((string) $key, 'HTTP_')) {
                    $key = str_replace(['_', ' '], '-', substr($key, 5));
                    $headers[$key] = $value;
                }
            }
        }

        // Lowerize names.
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
     * Prepare a global variable (without changing dotted names).
     *
     * @param  string $name
     * @param  string $source
     * @param  bool   $json
     * @return array
     */
    private function prepareGlobalVariable(string $name, string $source = '', bool $json = false): array
    {
        $encode = false;

        switch ($name) {
            case 'GET':
                $source = (string) ($_SERVER['QUERY_STRING'] ?? '');
                $encode = true;
                break;
            case 'POST':
                // This is checked in constructor via content-type header.
                if ($json) {
                    return (array) json_decode($source, flags: JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING);
                }
                break;
            case 'COOKIE':
                if (!empty($_SERVER['HTTP_COOKIE'])) {
                    $source = implode('&', array_map('trim', explode(';', (string) $_SERVER['HTTP_COOKIE'])));
                }
                break;
        }

        return Util::parseQueryString($source, $encode);
    }
}
