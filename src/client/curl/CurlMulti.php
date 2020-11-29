<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client\curl;

use froq\http\client\{Client, CurlException};

/**
 * Curl Multi.
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\CurlMulti
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
final class CurlMulti
{
    /**
     * Clients.
     * @var array<froq\http\client\Client>
     */
    protected array $clients;

    /**
     * Constructor.
     * @param  array<froq\http\client\Client>|null $clients
     * @throws froq\http\client\CurlException
     */
    public function __construct(array $clients = null)
    {
        if (!extension_loaded('curl')) {
            throw new CurlException('curl module not loaded');
        }

        $clients && $this->setClients($clients);
    }

    /**
     * Set clients.
     * @param  array<froq\http\client\Client> $clients
     * @return self
     * @throws froq\http\client\CurlException
     */
    public function setClients(array $clients): self
    {
        foreach ($clients as $client) {
            if (!$client instanceof Client) {
                throw new CurlException('Each client must be instance of "%s", "%s" given',
                    [Client::class, is_object($client) ? get_class($client) : gettype($client)]);
            }

            $this->clients[] = $client;
        }

        return $this;
    }

    /**
     * Get clients.
     * @return ?array<froq\http\client\Client>
     */
    public function getClients(): ?array
    {
        return $this->clients ?? null;
    }

    /**
     * Run.
     * @return void
     */
    public function run(): void
    {
        $clients = $this->clients;
        if (empty($clients)) {
            throw new CurlException('No clients initiated yet to process');
        }

        $multiHandle = curl_multi_init();
        if (!$multiHandle) {
            throw new CurlException('Failed to initialize multi-curl session [error: %s]', '@error');
        }

        $clientStack = [];

        foreach ($clients as $client) {
            $client->prepare();

            $curl = $client->getCurl();
            $curl->applyOptions();

            $handle = $curl->getHandle();

            $error = curl_multi_add_handle($multiHandle, $handle);
            if ($error) {
                throw new CurlException(curl_multi_strerror($error), $error);
            }

            // Tick.
            $clientStack[(int) $handle] = $client;
        }

        // Exec wrapper (http://php.net/curl_multi_select#108928).
        $exec = function ($multiHandle, &$running) {
            while (curl_multi_exec($multiHandle, $running) == CURLM_CALL_MULTI_PERFORM);
        };

        // Start requests.
        $exec($multiHandle, $running);

        do {
            // Wait a while if fail. Note: This must be here to achieve the winner (fastest) response
            // first in a right way, not in $exec loop like http://php.net/curl_multi_exec#113002.
            if (curl_multi_select($multiHandle) == -1) {
                usleep(1);
            }

            // Get new state.
            $exec($multiHandle, $running);

            while ($info = curl_multi_info_read($multiHandle)) {
                // Check tick.
                $client = $clientStack[(int) $info['handle']];
                if ($client == null) {
                    continue;
                }

                $handle = $info['handle'];
                if ($handle != $client->getCurl()->getHandle()) {
                    continue;
                }

                // Check status.
                $ok = ($info['result'] == CURLE_OK && $info['msg'] == CURLMSG_DONE);

                $result = $ok ? curl_multi_getcontent($handle) : false;
                if ($result !== false) {
                    $client->end($result, curl_getinfo($handle), null);
                } else {
                    $client->end(null, null, new CurlError(curl_error($handle), null, $info['result']));
                }

                curl_multi_remove_handle($multiHandle, $handle);
                curl_close($handle);

                // This can be set true to break the queue.
                if ($client->aborted) {
                    $client->fireEvent('abort');

                    break 2; // Break parent loop.
                }
            }
        } while ($running);

        // Close handles if exist any more (which might be not closed due to client abort).
        foreach ($clientStack as $client) {
            $handle = $client->getCurl()->getHandle();
            if (is_resource($handle)) {
                curl_multi_remove_handle($multiHandle, $handle);
                curl_close($handle);
            }
        }

        curl_multi_close($multiHandle);
    }
}
