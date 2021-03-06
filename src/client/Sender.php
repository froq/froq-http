<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\{Client, Response};
use froq\http\client\curl\{Curl, CurlMulti};

/**
 * Sender.
 *
 * @package froq\http\client
 * @object  froq\http\client\Sender
 * @author  Kerem Güneş
 * @since   3.0, 4.0 Renamed from MessageEmitter.
 * @static
 */
final class Sender
{
    /**
     * Send a request with a single client.
     *
     * @param  froq\http\client\Client $client
     * @return froq\http\client\Response
     */
    public static function send(Client $client): Response
    {
        $curl = new Curl($client);
        $client->setCurl($curl);

        $curl->run();

        return $client->getResponse();
    }

    /**
     * Send multi requests with multi clients.
     *
     * @param  array<froq\http\client\Client> $clients
     * @return array<froq\http\client\Response>
     */
    public static function sendMulti(array $clients): array
    {
        foreach ($clients as $client) {
            $client->setCurl(new Curl($client));
        }

        $curlm = new CurlMulti($clients);
        $curlm->run();

        $responses = [];

        foreach ($curlm->getClients() as $client) {
            $responses[] = $client->getResponse();
        }

        return $responses;
    }
}
