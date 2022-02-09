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
 * Curl Multi.
 *
 * A class for handling multiple cURL opearations & feeding back client.
 *
 * @package froq\http\client\curl
 * @object  froq\http\client\curl\CurlMulti
 * @author  Kerem Güneş
 * @since   3.0
 */
final class CurlMulti
{
    /** @var array<froq\http\client\Client> */
    protected array $clients;

    /**
     * Constructor.
     *
     * @param array<froq\http\client\Client>|null $clients
     */
    public function __construct(array $clients = null)
    {
        $clients && $this->setClients($clients);
    }

    /**
     * Set clients.
     *
     * @param  array<froq\http\client\Client> $clients
     * @return void
     * @throws froq\http\client\curl\CurlException
     */
    public function setClients(array $clients): void
    {
        foreach ($clients as $client) {
            ($client instanceof Client) || throw new CurlException(
                'Each client must be instance of %s, %t given',
                [Client::class, $client]
            );

            $this->clients[] = $client;
        }
    }

    /**
     * Get clients.
     *
     * @return array<froq\http\client\Client>|null
     */
    public function getClients(): array|null
    {
        return $this->clients ?? null;
    }

    /**
     * Run a multi cURL request.
     *
     * @return void
     * @throws froq\http\client\curl\CurlException
     */
    public function run(): void
    {
        $clients = $this->getClients();
        $clients || throw new CurlException('No clients initiated yet to process');

        $multiHandle = curl_multi_init();
        $multiHandle || throw new CurlException('Failed multi-curl session [error: %s]', '@error');

        $stack = [];

        foreach ($clients as $client) {
            $client->setup();

            $curl   = $client->getCurl();
            $handle = $curl->init();


            $result = curl_multi_add_handle($multiHandle, $handle);
            if ($result != CURLM_OK) {
                throw new CurlException(curl_multi_strerror($result), $result);
            }

            // Tick for check & drop.
            $curl->handle =& $handle;

            $stack[(int) $handle] = [$client, $curl];
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
                $id = (int) $info['handle'];
                @ [$client, $curl] = $stack[$id];

                // Check tick.
                if (!$client || $curl->handle != $info['handle']) {
                    continue;
                }

                // Check status.
                $ok     = $info['result'] == CURLE_OK && $info['msg'] == CURLMSG_DONE;
                $handle = $info['handle'];

                $result = $ok ? curl_multi_getcontent($handle) : false;
                if ($result !== false) {
                    $client->end($result, $curl->getHandleInfo($handle), null);
                } else {
                    $client->end(null, null, new CurlError(curl_error($handle), null, $info['result']));
                }

                // This can be set true to break the queue.
                if ($client->aborted) {
                    $client->fireEvent('abort');

                    // Break upper loop.
                    break 2;
                }
            }
        } while ($running);

        // Drop handles if any more, those might be not closed due to client abort.
        foreach ($stack as $id => [, $curl]) {
            if (isset($curl->handle) && $curl->handle instanceof CurlHandle) {
                curl_multi_remove_handle($multiHandle, $curl->handle);
                unset($curl->handle, $stack[$id]);
            }
        }

        // Drop handle.
        unset($multiHandle);
    }
}
