# JSON RPC v2 client for PHP

Flexible JSON RPC v2 client for PHP.

_Library is in active development_

## Hot to use

### Creating client

Simplest way to create JSON RPC client is to use factory

```php
use Strider2038\JsonRpcClient\ClientFactory;

$factory = new ClientFactory();

// HTTP client
$client = $factory->createClient('http://localhost:3000/rpc', ['timeout_ms' => 2000]);

// TCP client
$client = $factory->createClient('tcp://localhost:3000', ['timeout_ms' => 2000]);
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

## Roadmap for v0.2

* [ ] client builder
* [ ] integration testing for http transport with server mock
* [ ] http authentication tests
* [ ] json array serializer
* [ ] bridge for symfony serializer
* [ ] bridge for jms serializer
* [ ] middleware interfaces
* [ ] add http transport support via psr-18
