<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\{ClientException, Request, Response};
use froq\http\client\curl\{Curl, CurlError};
use froq\common\traits\OptionTrait;
use froq\http\Util as HttpUtil;
use froq\event\Events;

/**
 * Client.
 *
 * Represents a client object that interacts via cURL library with the remote servers using only
 * HTTP protocols. Hence it should not be used for other protocols and should be ensure that cURL
 * library is available.
 *
 * @package froq\http\client
 * @object  froq\http\client\Client
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
final class Client
{
    /**
     * Option trait.
     *
     * @see froq\common\traits\OptionTrait
     * @since 4.0
     */
    use OptionTrait;

    /**
     * Request, simply an HTTP-Message object that filled after send calls.
     *
     * @var froq\http\client\Request
     */
    private Request $request;

    /**
     * Response, simply an HTTP-Message object that filled after send calls.
     *
     * @var froq\http\client\Response
     */
    private Response $response;

    /**
     * Curl object that runs cURL operations.
     *
     * @var froq\http\client\curl\Curl
     */
    private Curl $curl;

    /**
     * Error that will be set if any cURL error occurs.
     *
     * @var froq\http\client\curl\CurlError
     */
    private CurlError $error;

    /**
     * Result that will be filled after cURL requests (if `options.keepResult` true).
     *
     * @var ?string
     */
    private ?string $result = null;

    /**
     * Result info that will be filled after cURL requests (if `options.keepResultInfo` true).
     *
     * @var ?array<string, any>
     */
    private ?array $resultInfo = null;

    /**
     * Default options.
     *
     * @var array<string, any>
     */
    private static array $optionsDefault = [
        'redirs'      => true,  'redirsMax'      => 3,
        'timeout'     => 5,     'timeoutConnect' => 3,
        'keepResult'  => true,  'keepResultInfo' => true,
        'method'      => 'GET', 'curl'           => null, // Curl options.
        'throwErrors' => false,
    ];

    /**
     * Events that can be fired for end, error or abort states.
     *
     * @var froq\event\Events
     * @since 4.0
     */
    private Events $events;

    /**
     * Tick for only multi (async) requests to break the client queue, @see `CurlMulti.run()`.
     *
     * @var bool
     * @since 4.0
     */
    public bool $aborted = false;

    /**
     * Constructor.
     *
     * @param string|null                  $url
     * @param array<string, any>|null      $options
     * @param array<string, callable>|null $events
     */
    public function __construct(string $url = null, array $options = null, array $events = null)
    {
        // Just as a syntactic sugar, URL is a parameter.
        $options = ['url' => $url] + ($options ?? []);
        $options = array_replace_recursive(self::$optionsDefault, $options);
        $this->setOptions($options);

        $this->events = new Events();
        if ($events != null) {
            foreach ($events as $name => $callback) {
                $this->events->add($name, $callback);
            }
        }
    }

    /**
     * Call magic (proxy) for only send() method that provides shortcuts to the HTTP-methods such
     * get, post, put etc. Throws a `ClientException` if no applicable methods given.
     *
     * @param  string $call
     * @param  array  $callArgs
     * @return froq\http\client\Response
     * @throws froq\http\client\ClientException
     */
    public function __call(string $call, array $callArgs): Response
    {
        static $calls = ['head', 'options', 'get', 'post', 'put', 'patch', 'delete'];

        if (!in_array($call, $calls)) {
            throw new ClientException('Invalid method call as "%s", valids are: %s',
                [$call, join(', ', $calls)]);
        }

        $callArgs = $callArgs ?: [null];

        // Method is discarded/overriden for such calls eg: get([url: ...]).
        if (is_array($callArgs[0])) {
            return $this->send(['method' => $call] + $callArgs[0]);
        }

        return $this->send($call, ...$callArgs);
    }

    /**
     * Sets the Curl object created by Sender object.
     *
     * @param  froq\http\client\curl\Curl
     * @return self
     */
    public function setCurl(Curl $curl): self
    {
        $this->curl = $curl;

        return $this;
    }

    /**
     * Gets the Curl object created by Sender object. This method should not be called before
     * send calls, otherwise a `ClientException` will be thrown.
     *
     * @return froq\http\client\curl\Curl
     * @throws froq\http\client\ClientException
     */
    public function getCurl(): Curl
    {
        if (isset($this->curl)) {
            return $this->curl;
        }

        throw new ClientException('Cannot access $curl property before send calls');
    }

    /**
     * Gets the error if any failure was occured while cURL execution.
     *
     * @return ?froq\http\client\curl\CurlError
     */
    public function getError(): ?CurlError
    {
        return $this->error ?? null;
    }

    /**
     * Gets the request object filled after send calls. This method should not be called before
     * send calls, otherwise a `ClientException` will be thrown.
     *
     * @return froq\http\client\Request
     * @throws froq\http\client\ClientException
     */
    public function getRequest(): Request
    {
        if (isset($this->request)) {
            return $this->request;
        }

        throw new ClientException('Cannot access $request property before send calls');
    }

    /**
     * Gets the response object filled after send calls. This method should not be called before
     * send calls, otherwise a `ClientException` will be thrown.
     *
     * @return froq\http\client\Response
     * @throws froq\http\client\ClientException
     */
    public function getResponse(): Response
    {
        if (isset($this->response)) {
            return $this->response;
        }

        throw new ClientException('Cannot access $response property before send calls');
    }

    /**
     * Gets result that filled after send calls. If any error occurs after calls or
     * `options.keepResult` is false returns null.
     *
     * @return ?string
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * Gets result info that filled after send calls. If any error occurs after calls or
     * `options.keepResultInfo` is false returns null.
     *
     * @return ?array
     */
    public function getResultInfo(): ?array
    {
        return $this->resultInfo;
    }

    /**
     * Send a request with given arguments. This method is a shortcut method for operations such
     * send-a-request then get-a-response.
     *
     * @param  string|array|null $method
     * @param  string|null       $url
     * @param  array|null        $urlParams
     * @param  string|null       $body
     * @param  array|null        $headers
     * @return froq\http\client\Response
     */
    public function send($method = null, string $url = null, array $urlParams = null,
        string $body = null, array $headers = null): Response
    {
        // Eg: send([method: GET, url: ..., ]) or get([url: ...]).
        if (is_array($method)) {
            @ ['method' => $method, 'url' => $url, 'urlParams' => $urlParams,
               'body' => $body, 'headers' => $headers] = $method;
        } elseif (!is_string($method)) {
            throw new ClientException('Invalid $method argument for %s(), valids are: '.
                'string, array but %s given', [__method__, gettype($method)]);
        }

        // May be set via setOption() separately.
        $method    = $method ?: $this->getOption('method');
        $url       = $url ?: $this->getOption('url');
        $urlParams = array_replace_recursive($this->getOption('urlParams', []), $urlParams ?: []);
        $body      = $body ?: $this->getOption('body');
        $headers   = array_replace_recursive($this->getOption('headers', []), $headers ?: []);

        $this->setOptions(['method' => $method, 'url' => $url, 'urlParams' => $urlParams,
            'body' => $body, 'headers' => $headers]);

        return Sender::send($this);
    }

    /**
     * Prepare is an internal method and called by `Curl` and `CurlMulti` before cURL operations
     * starts in `run()` method, for both single and multi (async) clients. Throws a `ClientException`
     * if no method, no URL or an invalid URL given.
     *
     * @return void
     * @throws froq\http\client\ClientException
     * @internal
     */
    public function prepare(): void
    {
        [$method, $url, $urlParams, $body, $headers] = $this->getOptions(['method', 'url',
            'urlParams', 'body', 'headers']);

        if ($method == null) throw new ClientException('No method given');
        if ($url == null) throw new ClientException('No URL given');

        // Reproduce URL structure.
        $temp = HttpUtil::parseUrl($url);
        if (empty($temp[0])) {
            throw new ClientException('No valid URL given, only "http" and "https" URLs are '.
                'accepted (given url: "%s")', [$url]);
        }

        $url = $temp[0];
        $urlParams = array_replace_recursive(($temp[1] ?? []), ($urlParams ?? []));
        if ($urlParams != null) {
            $url = $url .'?'. HttpUtil::buildQuery($urlParams);
        }

        // Create message objects.
        $this->request = new Request($method, $url, $urlParams, $body, $headers);
        $this->response = new Response();
    }

    /**
     * End is an internal method and called by `Curl` and `CurlMulti` after cURL operations end
     * in `run()` method, for both single and multi (async) clients.
     *
     * @param  ?string                     $result
     * @param  ?array                      $resultInfo
     * @param  ?froq\http\client\CurlError $error
     * @return void
     * @internal
     */
    public function end(?string $result, ?array $resultInfo, ?CurlError $error): void
    {
        if ($error == null) {
            // Finalize request headers.
            $headers = HttpUtil::parseHeaders($resultInfo['request_header'], true);
            if (empty($headers) || empty($headers[0])) {
                return;
            }

            // These options can be disabled for memory-wise apps.
            [$keepResult, $keepResultInfo] = $this->getOptions(['keepResult', 'keepResultInfo']);

            if ($keepResult) {
                $this->result = $result;
            }

            if ($keepResultInfo) {
                // Add url stuff.
                $resultInfo['finalUrl'] = $resultInfo['url'];
                $resultInfo['refererUrl'] = $headers['referer'] ?? null;
                $resultInfo['contentType'] = $resultInfo['contentCharset'] = null;

                // Add content stuff.
                if (isset($resultInfo['content_type'])) {
                    sscanf(''. $resultInfo['content_type'], '%[^;];%[^=]=%[^$]',
                        $contentType, $_, $contentCharset);

                    $resultInfo['contentType'] = $contentType;
                    $resultInfo['contentCharset'] = $contentCharset ? strtolower($contentCharset) : null;
                }

                $this->resultInfo = $resultInfo;
            }

            sscanf($headers[0], '%s %s %[^$]', $_, $_, $httpVersion);

            // Http version can be modified with CURLOPT_HTTP_VERSION, so here we update to provide
            // an accurate result for viewing or dumping purposes (eg: echo $client->getRequest()).
            $this->request->setHttpVersion($httpVersion)
                          ->setHeaders($headers, true);

            // Checker for redirections etc. (for finding final HTTP-Message).
            $nextCheck = function ($body) {
                return ($body && strpos($body, 'HTTP/') === 0);
            };

            @ [$headers, $body] = explode("\r\n\r\n", $result, 2);
            if ($nextCheck($body)) {
                do {
                    @ [$headers, $body] = explode("\r\n\r\n", $body, 2);
                } while ($nextCheck($body));
            }

            $headers = HttpUtil::parseHeaders($headers, true);
            if (empty($headers) || empty($headers[0])) {
                return;
            }

            sscanf($headers[0], '%s %d', $httpVersion, $status);

            $this->response->setHttpVersion($httpVersion)
                           ->setHeaders($headers)
                           ->setStatus($status);

            if ($body != null) {
                @ ['content-encoding' => $contentEncoding,
                   'content-type'     => $contentType] = $headers;

                // Decode gzip (if gzip'ed).
                if ($contentEncoding == 'gzip') {
                    $body = gzdecode($body);
                }

                $this->response->setBody($body);

                // Decode JSON (if json'ed).
                if ($contentType && strpos($contentType, 'json')) {
                    $parsedBody = json_decode($body, null, 512, JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING);

                    if ($parsedBody !== null) {
                        $this->response->setParsedBody($parsedBody);
                    }
                }
            }
        } else {
            // Discards error event below.
            if ($this->getOption('throwErrors') == true) {
                throw $error;
            }

            $this->error = $error;

            // Call error event if exists.
            $this->fireEvent('error');
        }

        // Call end event if exists.
        $this->fireEvent('end');
    }

    /**
     * Fire an event that was set in options. The only names that called are limited to: "end",
     * "error" and "abort".
     * - end: always fired when the cURL execution and request finish.
     * - error: fired when a cURL error occurs.
     * - abort: fired when an abort operation occurs. To achieve this, so break client queue, a
     * callback must be defined in for breaker client and set client `$aborted` property as true in
     * that callback.
     *
     * @param  string $name
     * @return void
     * @internal
     */
    public function fireEvent(string $name): void
    {
        $event = $this->events->get($name);
        if ($event != null) {
            $event($this);
        }
    }
}
