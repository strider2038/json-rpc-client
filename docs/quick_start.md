# Quick start

## Creating a client

Simplest way to create JSON RPC client is to use factory

```php
use Strider2038\JsonRpcClient\ClientFactory;

$clientFactory = new ClientFactory();

// HTTP client with Authorization header
// Don't forget to install Guzzle or Symfony Http Client
$client = $clientFactory->createClient('http://localhost:3000/rpc', [
    'request_timeout_us'      => 1000000,
    'transport_configuration' => [
        'headers' => [
            'Authorization' => 'Bearer secret_token',
        ],
    ]
]);

// TCP socket client
$client = $clientFactory->createClient('tcp://localhost:3000', [
    'request_timeout_us' => 1000000,
    'connection'         => [
        'attempt_timeout_us' => 100000,
        'timeout_multiplier' => 2.0,
        'max_attempts'       => 5,
    ],
]);

// Unix socket client
$client = $clientFactory->createClient('unix://var/run/jsonrpc.sock', [
    'request_timeout_us' => 1000000,
    'connection'         => [
        'attempt_timeout_us' => 100000,
        'timeout_multiplier' => 2.0,
        'max_attempts'       => 5,
    ],
]);
```

## Calling remote procedures

```php
// remote procedure call with positional parameters
$result = $client->call('sum', [1, 2, 4]);

// $result = 7

// remote procedure call with object parameters
$params = new \stdClass();
$params->subtrahend = 23;
$params->minuend = 42;

$result = $client->call('subtract', $params);

// $result can be an object
// $result->subtracted = 19;

// notification without result
$client->notify('notify', [100]);

// batch call
$result = $client->batch()
    ->call('sum', [1, 2, 4])
    ->call('subtract', $params)
    ->notify('notify', [100])
    ->call('multiply', [3, 5])
    ->send();

// $result is a sorted array of RPC results
// $result = [
//      7,
//      $object,
//      null,
//      15,
// ]
```
