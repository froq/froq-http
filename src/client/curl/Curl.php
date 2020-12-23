<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client\curl;

use froq\http\client\curl\{CurlError, CurlException};
use froq\http\client\Client;
use CurlHandle;

/**
 * Curl.
 *
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\Curl
 * @author  Kerem Güneş
 * @since   3.0
 */
final class Curl
{
    /** @var froq\http\client\Client */
    private Client $client;

    /** @const array */
    public const BLOCKED_OPTIONS = [
        'CURLOPT_CUSTOMREQUEST'  => 10036,
        'CURLOPT_URL'            => 10002,
        'CURLOPT_HEADER'         => 42,
        'CURLOPT_RETURNTRANSFER' => 19913,
        'CURLINFO_HEADER_OUT'    => 2,
    ];

    /**
     * Constructor.
     *
     * @param froq\http\client\Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $client && $this->setClient($client);
    }

    /**
     * Set client.
     *
     * @param  froq\http\client\Client $client
     * @return self
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return froq\http\client\Client|null
     */
    public function getClient(): Client|null
    {
        return $this->client ?? null;
    }

    /**
     * Run a cURL request.
     *
     * @return void
     * @throws froq\http\client\curl\CurlException
     */
    public function run(): void
    {
        $client = $this->getClient();
        $client || throw new CurlException('No client initiated yet to process');

        $client->setup();

        $handle =& $this->init();

        $result = curl_exec($handle);
        if ($result !== false) {
            $client->end($result, curl_getinfo($handle), null);
        } else {
            $client->end(null, null, new CurlError(curl_error($handle), null, curl_errno($handle)));
        }

        $handle = null;
    }

    /**
     * Init a cURL handle.
     *
     * @return &CurlHandle
     * @throws froq\http\client\curl\CurlException
     */
    public function &init(): CurlHandle
    {
        $client = $this->getClient();
        $client || throw new CurlException('No client initiated yet to process');

        $handle = curl_init();
        $handle || throw new CurlException('Failed curl session [error: %s]', '@error');

        $request = $client->getRequest();

        [$method, $url, $headers, $body, $clientOptions] = [
            $request->getMethod(), $request->getUrl(),
            $request->getHeaders(), $request->getBody(),
            $client->getOptions()
        ];

        $options = [
            // Immutable (internal) options.
            CURLOPT_CUSTOMREQUEST     => $method, // Prepared, set by request object.
            CURLOPT_URL               => $url,    // Prepared, set by request object.
            CURLOPT_HEADER            => true,    // For proper response headers & body split.
            CURLOPT_RETURNTRANSFER    => true,    // For proper response headers & body split.
            CURLINFO_HEADER_OUT       => true,    // For proper request headers split.
            // Mutable (client) options.
            CURLOPT_AUTOREFERER       => true,
            CURLOPT_FOLLOWLOCATION    => (bool) $clientOptions['redirs'],
            CURLOPT_MAXREDIRS         => (int)  $clientOptions['redirsMax'],
            CURLOPT_SSL_VERIFYHOST    => false,
            CURLOPT_SSL_VERIFYPEER    => false,
            CURLOPT_DEFAULT_PROTOCOL  => 'http',
            CURLOPT_DNS_CACHE_TIMEOUT => 3600, // 1 hour.
            CURLOPT_TIMEOUT           => (int) $clientOptions['timeout'],
            CURLOPT_CONNECTTIMEOUT    => (int) $clientOptions['timeoutConnect'],
        ];

        // Request headers.
        $options[CURLOPT_HTTPHEADER][] = 'Expect:';
        foreach ($headers as $name => $value) {
            $options[CURLOPT_HTTPHEADER][] = $name .': '. $value;
        }

        // If body provided, Content-Type & Content-Length added automatically by curl.
        // Else we add them manually, if method is suitable for this.
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
            $options[CURLOPT_HTTPHEADER][] = 'Content-Length: '. strlen((string) $body);
        }

        // Extra cURL options.
        $clientOptionsCurl = null;

        if (isset($clientOptions['curl'])) {
            is_array($clientOptions['curl']) || throw new CurlException(
                'Options `curl` field must be array|null, %s given', get_type($clientOptions['curl'])
            );
            $clientOptionsCurl = $clientOptions['curl'];
        }

        // Somehow HEAD method is freezing requests and causing timeouts.
        if ($method == 'HEAD') {
            $clientOptionsCurl[CURLOPT_NOBODY] = true;
        }

        // Assign HTTP version if provided.
        if (isset($clientOptions['httpVersion'])) {
            $clientOptionsCurl[CURLOPT_HTTP_VERSION] = match ((string) $clientOptions['httpVersion']) {
                '2', '2.0' => CURL_HTTP_VERSION_2_0,
                '1.1'      => CURL_HTTP_VERSION_1_1,
                '1.0'      => CURL_HTTP_VERSION_1_0,
                default    => throw new CurlException('Invalid `httpVersion` option `%s`, valids are: '
                    . '2, 2.0, 1.1, 1.0', $clientOptions['httpVersion'])
            };
        }

        // Apply user-provided options.
        if (isset($clientOptionsCurl)) {
            // // HTTP/2 requires a https scheme.
            // if (isset($clientOptionsCurl[CURLOPT_HTTP_VERSION])
            //     && $clientOptionsCurl[CURLOPT_HTTP_VERSION] == CURL_HTTP_VERSION_2_0
            //     && !str_starts_with($url, 'https')) {
            //     throw new CurlException('URL scheme must be `https` for HTTP/2 requests');
            // }

            foreach ($clientOptionsCurl as $name => $value) {
                // Check constant name.
                if (!$name || !is_int($name)) {
                    throw new CurlException('Invalid cURL constant `%s`', $name);
                }

                // Check for internal options.
                if (self::optionCheck($name, $foundName)) {
                    throw new CurlException(
                        'Not allowed cURL option %s given [tip: some options are set internally and '.
                        'not allowed for a proper request/response process, not allowed options are: '.
                        '%s]', [$foundName, join(', ', array_keys(self::BLOCKED_OPTIONS))]
                    );
                }

                if (is_array($value)) {
                    foreach ($value as $value) {
                        $options[$name][] = $value;
                    }
                } else {
                    $options[$name] = $value;
                }
            }
        }

        if (curl_setopt_array($handle, $options)) {
            return $handle;
        }

        throw new CurlException('Failed to apply cURL options [error: %s]', '@error');
    }

    /**
     * Check option validity.
     *
     * @param  any          $searchValue
     * @param  string|null &$foundName
     * @return bool
     */
    private static function optionCheck($searchValue, string &$foundName = null): bool
    {
        // Check options if contain search value.
        foreach (self::BLOCKED_OPTIONS as $name => $value) {
            if ($searchValue === $value) {
                $foundName = $name;
                break;
            }
        }

        return ($foundName != null);
    }
}
