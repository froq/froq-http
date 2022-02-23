<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\curl\{Curl, CurlError, CurlResponseError};
use froq\common\trait\OptionTrait;
use froq\event\Events;

/**
 * Client.
 *
 * Represents a client object that interacts via cURL library with the remote servers using only HTTP protocols.
 * Hence it should not be used for other protocols and should be ensure that cURL library is available.
 *
 * @package froq\http\client
 * @object  froq\http\client\Client
 * @author  Kerem Güneş
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
    private CurlError|CurlResponseError|null $error;

    /** @var ?string */
    private ?string $result = null;

    /** @var ?array */
    private ?array $resultInfo = null;

    /** @var array */
    private static array $optionsDefault = [
        'redirs'      => true,  'redirsMax'       => 3,
        'timeout'     => 5,     'timeoutConnect'  => 3,
        'keepResult'  => true,  'keepResultInfo'  => true,
        'throwErrors' => false, 'throwHttpErrors' => false,
        'httpVersion' => null,  'userpass'        => null,
        'gzip'        => true,  'json'            => false,
        'method'      => 'GET', 'curl'            => null, // Curl options.
    ];

    /** @var froq\event\Events */
    private Events $events;

    /** @var bool */
    public bool $sent = false;

    /** @var bool */
    public bool $abort = false;

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
        if ($events) {
            foreach ($events as $name => $callback) {
                $this->events->add($name, $callback);
            }
        }
    }

    /**
     * Set curl object created by Sender class.
     *
     * @param  froq\http\client\curl\Curl
     * @return self
     * @internal
     */
    public function setCurl(Curl $curl): self
    {
        $this->curl = $curl;

        return $this;
    }

    /**
     * Get curl object created by Sender class.
     *
     * Note: This method should not be called before send calls, a `ClientException` will be thrown
     * otherwise.
     *
     * @return froq\http\client\curl\Curl
     * @throws froq\http\client\ClientException
     */
    public function getCurl(): Curl
    {
        $this->sent || throw new ClientException(
            'Cannot access $curl property before send calls'
        );

        return $this->curl;
    }

    /**
     * Get error if any failure was occured while cURL execution.
     *
     * Note: This method should not be called before send calls, a `ClientException` will be thrown
     * otherwise.
     *
     * @return froq\http\client\curl\{CurlError|CurlResponseError}
     * @throws froq\http\client\ClientException
     */
    public function getError(): CurlError|CurlResponseError|null
    {
        $this->sent || throw new ClientException(
            'Cannot access $error property before send calls'
        );

        return $this->error;
    }

    /**
     * Get request property that set after send calls.
     *
     * Note: This method should not be called before send calls, a `ClientException` will be thrown
     * otherwise.
     *
     * @return froq\http\client\Request
     * @throws froq\http\client\ClientException
     */
    public function getRequest(): Request
    {
        $this->sent || throw new ClientException(
            'Cannot access $request property before send calls'
        );

        return $this->request;
    }

    /**
     * Get response property that set after send calls.
     *
     * Note: This method should not be called before send calls, a `ClientException` will be thrown
     * otherwise.
     *
     * @return froq\http\client\Response
     * @throws froq\http\client\ClientException
     */
    public function getResponse(): Response
    {
        $this->sent || throw new ClientException(
            'Cannot access $response property before send calls'
        );

        return $this->response;
    }

    /**
     * Get result property that set after send calls.
     *
     * Note: If any error occurs after calls or `options.keepResult` is false returns null.
     *
     * @return string|null
     */
    public function getResult(): string|null
    {
        return $this->result;
    }

    /**
     * Get result info property that set after send calls.
     *
     * Note: If any error occurs after calls or `options.keepResultInfo` is false returns null.
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
     * @param  array|null  $query  Alias for $urlParams
     * @return froq\http\client\Response
     */
    public function send(string $method = null, string $url = null, array $urlParams = null,
        string|array $body = null, array $headers = null, array $query = null): Response
    {
        // May be set via setOption().
        $method    = $method ?: $this->getOption('method');
        $url       = $url    ?: $this->getOption('url');
        $urlParams = array_replace_recursive($this->getOption('urlParams', []), $urlParams ?: $query ?: []);
        $body      = $body   ?: $this->getOption('body');
        $headers   = array_replace_recursive($this->getOption('headers', []), $headers ?: []);

        $this->setOptions(['method' => $method, 'url' => $url, 'urlParams' => $urlParams, 'body' => $body,
            'headers' => $headers]);

        return Sender::send($this);
    }

    /**
     * Setup is an internal method and called by `Curl` and `CurlMulti` before cURL operations starts
     * in `run()` method, for both single and multi clients. Throws a `ClientException` if no method,
     * no URL or an invalid URL given.
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
        $parsedUrl = http_parse_url($url);
        if (!$parsedUrl) {
            throw new ClientException('Invalid URL `%s`', $url);
        }

        // Ensure scheme is http or https.
        if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
            throw new ClientException('Invalid URL `%s`, `http` or `https` scheme required', $url);
        }

        // Update params if given.
        if ($urlParams) {
            $urlParams = array_replace_recursive(
                (array) $parsedUrl['queryParams'], (array) $urlParams
            );
            $parsedUrl['queryParams'] = $urlParams;
        }

        $url = http_build_url($parsedUrl);

        $headers = array_lower_keys((array) $headers);

        // Disable GZip'ed responses.
        if (!$this->options['gzip']) {
            $headers['accept-encoding'] = null;
        }

        $contentType = null;
        if (isset($headers['content-type'])) {
            $contentType = $headers['content-type'] = strtolower($headers['content-type']);
        }

        // Add JSON header if options json is true.
        if ($this->options['json'] && (!$contentType || !str_contains($contentType, 'json'))) {
            $contentType = $headers['content-type'] = 'application/json';
        }

        // Encode body & add related headers if needed.
        if ($body && is_array($body)) {
            if ($contentType && str_contains($contentType, 'json')) {
                $body = json_encode($body, flags: (
                    JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
                ));
            } else {
                $body = http_build_query($body);
                $contentType = 'application/x-www-form-urlencoded';
            }

            $headers['content-type']   ??= $contentType;
            $headers['content-length'] ??= (string) strlen($body);
        }

        // Create message objects.
        $this->request  = new Request($method, $url, $urlParams, $body, $headers);
        $this->response = new Response(0, null, null, null);
    }

    /**
     * End is an internal method and called by `Curl` and `CurlMulti` after cURL operations end
     * in `run()` method, for both single and multi clients.
     *
     * @param  ?string                     $result
     * @param  ?array                      $resultInfo
     * @param  ?froq\http\client\CurlError $error
     * @return void
     * @internal
     */
    public function end(?string $result, ?array $resultInfo, ?CurlError $error = null): void
    {
        // These options can discard error event below.
        if ($error) {
            if ($this->options['throwErrors']) {
                throw $error;
            }
        } elseif (!$error && $resultInfo['http_code'] >= 400) {
            $error = new CurlResponseError($resultInfo['http_code']);
            if ($this->options['throwHttpErrors']) {
                throw $error;
            }
        }

        $this->error = $error;

        if ($resultInfo) {
            $headers = http_parse_headers($resultInfo['request_header']);
            if (!$headers) {
                return;
            }

            if ($this->options['keepResult']) {
                $this->result = $result;
            }
            if ($this->options['keepResultInfo']) {
                $resultInfo += ['finalUrl'    => null, 'refererUrl'     => null,
                                'contentType' => null, 'contentCharset' => null];

                $resultInfo['finalUrl']   = $resultInfo['url'];
                $resultInfo['refererUrl'] = $headers['Referer'] ?? $headers['referers'] ?? null;

                if (isset($resultInfo['content_type'])) {
                    sscanf($resultInfo['content_type'], '%[^;];%[^=]=%[^$]', $contentType, $_, $contentCharset);

                    $resultInfo['contentType']    = $contentType;
                    $resultInfo['contentCharset'] = $contentCharset ? strtolower($contentCharset) : null;
                }

                $this->resultInfo = $resultInfo;
            }

            $requestLine = http_parse_request_line($headers[0]);
            if (!$requestLine) {
                return;
            }

            // Http version can be modified with CURLOPT_HTTP_VERSION, so here we update to provide
            // an accurate result for viewing or dumping purposes (eg: echo $client->getRequest()).
            $this->request->setHttpProtocol($requestLine['protocol'])
                          ->setHttpVersion($requestLine['version'])
                          ->setHeaders($headers, reset: true);

            // @cancel
            // Checker for redirections etc. (for finding final HTTP-Message).
            // $next = fn($body) => $body && str_starts_with($body, 'HTTP/');

            // @ [$headers, $body] = explode("\r\n\r\n", $result, 2);
            // if ($next($body)) {
            //     do {
            //         @ [$headers, $body] = explode("\r\n\r\n", $body, 2);
            //     } while ($next($body));
            // }

            $headers = $resultInfo['response_header'];

            // Get last slice of multi headers (via redirections).
            if ($headers && str_contains($headers, "\r\n\r\n")) {
                $headers = last(explode("\r\n\r\n", $headers));
            }

            $headers = http_parse_headers($headers);
            if (!$headers) {
                return;
            }

            $responseLine = http_parse_response_line($headers[0]);
            if (!$responseLine) {
                return;
            }

            $this->response->setHttpProtocol($responseLine['protocol'])
                           ->setHttpVersion($responseLine['version'])
                           ->setStatus($responseLine['status'])
                           ->setHeaders($headers);

            // Set response body (and parsed body).
            if ($result != '') {
                $body = $result;
                $parsedBody = null;
                unset($result);

                [$contentEncoding, $contentType] = [
                    $this->response->getHeader('content-encoding'),
                    $this->response->getHeader('content-type'),
                ];

                // Decode GZip (if GZip'ed).
                if ($contentEncoding && str_contains($contentEncoding, 'gzip')) {
                    $decodedBody = gzdecode($body);
                    if (is_string($decodedBody)) {
                        $body = $decodedBody;
                    }
                    unset($decodedBody);
                }

                // Decode JSON (if JSON'ed).
                if ($contentType && str_contains($contentType, 'json')) {
                    $decodedBody = json_decode($body, flags: JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING);
                    if (is_array($decodedBody)) {
                        $parsedBody = $decodedBody;
                    }
                    unset($decodedBody);
                }

                $this->response->setBody($body);
                $this->response->setParsedBody($parsedBody);
            }
        }

        // Call error event if exists.
        if ($error) {
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
     * callback must be defined in for breaker client and set client `$abort` property as true in
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
