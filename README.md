# JSON RPC v2 client for PHP

[![Latest Stable Version](https://poser.pugx.org/strider2038/json-rpc-client/v/stable)](https://packagist.org/packages/strider2038/json-rpc-client)
[![Total Downloads](https://poser.pugx.org/strider2038/json-rpc-client/downloads)](https://packagist.org/packages/strider2038/json-rpc-client)
[![License](https://poser.pugx.org/strider2038/json-rpc-client/license)](https://packagist.org/packages/strider2038/json-rpc-client)
[![Build Status](https://travis-ci.org/strider2038/json-rpc-client.svg?branch=master)](https://travis-ci.org/strider2038/json-rpc-client)
[![Build Status](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/build.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/?branch=master)
[![StyleCI](https://github.styleci.io/repos/172254542/shield?branch=master)](https://github.styleci.io/repos/172254542)

Flexible JSON RPC v2 client for PHP.

_Library is in active development_

## Installation

Use composer to install library. It is recommended to fix minor version while library is under development.

```bash
composer require strider2038/json-rpc-client ^0.3
```

## Hot to use

### Creating client

Simplest way to create JSON RPC client is to use factory

```php
use Strider2038\JsonRpcClient\ClientFactory;

$factory = new ClientFactory();

// HTTP client with Authorization header
$client = $factory->createClient('http://localhost:3000/rpc', [
    'request_timeout_us'      => 1000000,
    'transport_configuration' => [
        'headers' => [
            'Authorization' => 'Bearer secret_token',
        ],
    ]
]);

// TCP client
$client = $factory->createClient('tcp://localhost:3000', [
    'request_timeout_us' => 1000000,
    'connection'         => [
        'attempt_timeout_us' => 100000,
        'timeout_multiplier' => 2.0,
        'max_attempts'       => 5,
    ],
]);
```

### Calling remote procedures

```php
// remote procedure call with positional parameters
$result = $client->call('sum', [1, 2, 4]);

// $result = 7

// remote procedure call with object parameters
$params = new \stdClass();
$params->subtrahend = 23;
$params->minuend = 42;

$result = $client->call('subtract', $params);

// $result can be object
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

// $result is sorted array of RPC results
// $result = [
//      7,
//      $object,
//      null,
//      15,
// ]
```

## Roadmap for next versions

* [x] client builder
* [x] integration testing for http transport with server mock
* [x] http authentication tests
* [x] json array serializer
* [x] unix socket transport
* [x] bridge for symfony serializer
* [x] ci test matrix
* [x] symfony http client transport
* [x] add http transport support via psr-18
* [x] error types by methods + default error type
* [ ] symfony bundle
* [ ] symfony bundle object annotations
* [ ] symfony example
* [ ] documentation
* [ ] caller context for possible authorization
* [ ] bridge for jms serializer
* [ ] web socket transport
