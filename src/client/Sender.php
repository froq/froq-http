<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
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
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0, 4.0 Renamed from MessageEmitter.
 * @static
 */
final class Sender
{
    /**
     * Send.
     * @param  froq\http\client\Client $client
     * @return froq\http\client\Response
     */
    public static function send(Client $client): Response
    {
        $curl = new Curl($client);
        $client->setCurl($curl);

        $runner = $curl;
        $runner->run();

        $response = $client->getResponse();

        return $response;
    }

    /**
     * Send async.
     * @param  array<froq\http\client\Client> $clients
     * @return array<froq\http\client\Response>
     */
    public static function sendAsync(array $clients): array
    {
        foreach ($clients as $client) {
            $curl = new Curl($client);
            $client->setCurl($curl);
        }

        $runner = new CurlMulti($clients);
        $runner->run();

        $responses = [];

        foreach ($runner->getClients() as $client) {
            $responses[] = $client->getResponse();
        }

        return $responses;
    }
}
