<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\{ClientException, Request, Response};
use froq\http\client\curl\{Curl, CurlError};
use froq\common\trait\OptionTrait;
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
     * @see froq\common\trait\OptionTrait
     * @since 4.0
     */
    use OptionTrait;

    /** @var froq\http\client\Request */
    private Request $request;

    /** @var froq\http\client\Response */
    private Response $response;

    /** @var froq\http\client\curl\Curl */
    private Curl $curl;

    /** @var froq\http\client\curl\CurlError */
    private CurlError $error;

    /** @var ?string */
    private ?string $result = null;

    /** @var ?array<string, any> */
    private ?array $resultInfo = null;

    /** @var array<string, any> */
    private static array $optionsDefault = [
        'redirs'      => true,  'redirsMax'      => 3,
        'timeout'     => 5,     'timeoutConnect' => 3,
        'keepResult'  => true,  'keepResultInfo' => true,
        'httpVersion' => null,  'throwErrors'    => false,
        'method'      => 'GET', 'curl'           => null, // Curl options.
    ];

    /** @var froq\event\Events */
    private Events $events;

    /** @var bool */
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

        $this->setOptions($options, self::$optionsDefault);

        $this->events = new Events();
        if ($events != null) {
            foreach ($events as $name => $callback) {
                $this->events->add($name, $callback);
            }
        }
    }

    /**
     * Set curl object created by sender object.
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
     * Get curl object created by sender object. This method should not be called before send calls,
     * otherwise a `ClientException` will be thrown.
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
     * Get error if any failure was occured while cURL execution.
     *
     * @return froq\http\client\curl\CurlError|null
     */
    public function getError(): CurlError|null
    {
        return $this->error ?? null;
    }

    /**
     * Get request object filled after send calls. This method should not be called before
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
     * Get response object filled after send calls. This method should not be called before
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
     * Get that filled after send calls. If any error occurs after calls or
     * `options.keepResult` is false returns null.
     *
     * @return string|null
     */
    public function getResult(): string|null
    {
        return $this->result;
    }

    /**
     * Get info that filled after send calls. If any error occurs after calls or
     * `options.keepResultInfo` is false returns null.
     *
     * @return array|null
     */
    public function getResultInfo(): array|null
    {
        return $this->resultInfo;
    }

    /**
     * Send a "HEAD" request.
     *
     * @see send()
     * @since 5.0
     */
    public function head(...$args)
    {
        return $this->send('HEAD', ...$args);
    }

    /**
     * Send a "GET" request.
     *
     * @see send()
     * @since 5.0
     */
    public function get(...$args)
    {
        return $this->send('GET', ...$args);
    }

    /**
     * Send a "POST" request.
     *
     * @see send()
     * @since 5.0
     */
    public function post(...$args)
    {
        return $this->send('POST', ...$args);
    }

    /**
     * Send a "PUT" request.
     *
     * @see send()
     * @since 5.0
     */
    public function put(...$args)
    {
        return $this->send('PUT', ...$args);
    }

    /**
     * Send a "DELETE" request.
     *
     * @see send()
     * @since 5.0
     */
    public function delete(...$args)
    {
        return $this->send('DELETE', ...$args);
    }

    /**
     * Send a request with given arguments. This method is a shortcut method for operations such
     * send-a-request then get-a-response.
     *
     * @param  string|null $method
     * @param  string|null $url
     * @param  array|null  $urlParams
     * @param  string|null $body
     * @param  array|null  $headers
     * @return froq\http\client\Response
     */
    public function send(string $method = null, string $url = null, array $urlParams = null,
        string|array $body = null, array $headers = null): Response
    {
        // May be set via setOption().
        $method    = $method ?: $this->getOption('method');
        $url       = $url    ?: $this->getOption('url');
        $urlParams = array_replace_recursive($this->getOption('urlParams', []), $urlParams ?: []);
        $body      = $body   ?: $this->getOption('body');
        $headers   = array_replace_recursive($this->getOption('headers', []), $headers ?: []);

        $this->setOptions(['method' => $method, 'url' => $url, 'urlParams' => $urlParams, 'body' => $body,
            'headers' => $headers]);

        return Sender::send($this);
    }

    /**
     * Setup is an internal method and called by `Curl` and `CurlMulti` before cURL operations starts
     * in `run()` method, for both single and multi (async) clients. Throw a `ClientException` if no
     * method, no URL or an invalid URL given.
     *
     * @return void
     * @throws froq\http\client\ClientException
     * @internal
     */
    public function setup(): void
    {
        [$method, $url, $urlParams, $body, $headers] = $this->getOptions(
            ['method', 'url', 'urlParams', 'body', 'headers']
        );

        $method || throw new ClientException('No method given');
        $url    || throw new ClientException('No URL given');

        // Reproduce URL structure.
        $temp = HttpUtil::parseUrl($url);
        if (empty($temp[0])) {
            throw new ClientException('No valid URL given, only http and https URLs are accepted'
                . ' [given: %s]', $url);
        }

        $url       = $temp[0];
        $urlParams = array_replace_recursive($temp[1] ?? [], $urlParams ?? []);
        if ($urlParams != null) {
            $url = $url .'?'. HttpUtil::buildQuery($urlParams);
        }

        // Encode body if needed.
        if ($body != null && is_array($body)) {
            if (isset($headers['content-type']) && str_contains($headers['content-type'], 'json')) {
                $body = json_encode($body);
            } else {
                $bool = http_build_query($body);
            }
        }

        // Create message objects.
        $this->request  = new Request($method, $url, $urlParams, $body, $headers);
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
            if (empty($headers[0])) {
                return;
            }

            // These options can be disabled for memory-wise apps.
            [$keepResult, $keepResultInfo] = $this->getOptions(['keepResult', 'keepResultInfo']);

            if ($keepResult) {
                $this->result = $result;
            }

            if ($keepResultInfo) {
                // Add url stuff.
                $resultInfo['finalUrl']    = $resultInfo['url'];
                $resultInfo['refererUrl']  = $headers['referer'] ?? null;
                $resultInfo['contentType'] = $resultInfo['contentCharset'] = null;

                // Add content stuff.
                if (isset($resultInfo['content_type'])) {
                    sscanf(''. $resultInfo['content_type'], '%[^;];%[^=]=%[^$]',
                        $contentType, $_, $contentCharset);

                    $resultInfo['contentType']    = $contentType;
                    $resultInfo['contentCharset'] = $contentCharset ? strtolower($contentCharset) : null;
                }

                $this->resultInfo = $resultInfo;
            }

            if (sscanf($headers[0], '%s %s %[^$]', $_, $_, $httpVersion) != 3) {
                return;
            }

            // Http version can be modified with CURLOPT_HTTP_VERSION, so here we update to provide
            // an accurate result for viewing or dumping purposes (eg: echo $client->getRequest()).
            $this->request->setHttpVersion($httpVersion)
                          ->setHeaders($headers, true);

            // Checker for redirections etc. (for finding final HTTP-Message).
            $nextCheck = fn($body) => $body && str_starts_with($body, 'HTTP/');

            @ [$headers, $body] = explode("\r\n\r\n", $result, 2);
            if ($nextCheck($body)) {
                do {
                    @ [$headers, $body] = explode("\r\n\r\n", $body, 2);
                } while ($nextCheck($body));
            }

            $headers = HttpUtil::parseHeaders($headers, true);
            if (empty($headers[0])) {
                return;
            }

            if (sscanf($headers[0], '%s %d', $httpVersion, $status) != 2) {
                return;
            }

            $this->response->setHttpVersion($httpVersion)
                           ->setHeaders($headers)
                           ->setStatus($status);

            if ($body != null) {
                // Decode gzip (if gzip'ed).
                if (isset($headers['content-encoding'])
                    && str_contains($headers['content-encoding'], 'gzip')) {
                    $body = gzdecode($body);
                }

                $this->response->setBody($body);

                // Decode JSON (if json'ed).
                if (isset($headers['content-type'])
                    && str_contains($headers['content-type'], 'json')) {
                    $parsedBody = json_decode($body, flags: JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING);
                    if ($parsedBody !== null) {
                        $this->response->setParsedBody($parsedBody);
                    }
                }
            }
        } else {
            // Discards error event below.
            if ($this->options['throwErrors']) {
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
     * @since  4.0
     * @internal
     */
    public function fireEvent(string $name): void
    {
        $event = $this->events->get($name);
        $event && $event($this);
    }
}
