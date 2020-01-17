#!/usr/bin/env php

<?php

/*
 * This file is part of JSON RPC Client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'./../../vendor/autoload.php';

use Strider2038\JsonRpcClient\ClientFactory;

$connection = getenv('TEST_TCP_TRANSPORT_URL');

$clientFactory = new ClientFactory();
$client = $clientFactory->createClient($connection, [
    'connection_timeout_us' => 5000000,
    'request_timeout_us'    => 5000000,
]);

while (true) {
    $timer = microtime(true);
    $result = $client->call('ping');
    $now = DateTime::createFromFormat('U.u', microtime(true));
    echo sprintf(
        '%s: ping response %s received in %d us%s', $now->format('Y-m-d H:i:s.u'),
        json_encode($result),
        (microtime(true) - $timer) * 1000 * 1000,
        PHP_EOL
    );

    sleep(1);
}
