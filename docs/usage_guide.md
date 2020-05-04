# Usage guide

## Response processing and error handling

By default, JSON RPC client processes all responses and returns response payload data. If server returns error response, then instance of `Strider2038\JsonRpcClient\Exception\ErrorResponseException` will be thrown. If you want to handle errors you have to catch exceptions.

```php
try {
    $client->call('method', $params);
} catch (\Strider2038\JsonRpcClient\Exception\ErrorResponseException $exception) {
    $error = $exception->getError();
    $error->getCode(); // contains JSON RPC error code
    $error->getMessage(); // contains JSON RPC error message
    $error->getData(); // contains JSON RPC error payload
}
```

Also, response processing mode automatically sorts results of batch call. So you can expect that all results will be properly sorted.

```php
$result = $client->batch()
    ->call('sum', [1, 2, 4])
    ->call('subtract', [7, 4])
    ->call('multiply', [3, 5])
    ->send();

// $result is a sorted array of RPC results
// $result = [
//      7,
//      3,
//      15,
// ]
```

If you want to have more control over responses you can disable response processing mode and client will return response objects instead of its payloads.

```php
$client = $clientFactory->createClient('http://localhost:3000/rpc', [
    'enable_response_processing' => false,
]);

/** @var \Strider2038\JsonRpcClient\Response\ResponseObjectInterface $response */
$response = $client->call('method', $params);

$response->getResult(); // contains JSON RPC response payload

// you can manually handle errors
if ($response->hasError()) {
    // method getError() should be called only if hasError() returns true, 
    // otherwise logic exception will be thrown
    $error = $response->getError();
}
```

Be aware! If response processing mode is disabled, then results of batch requests will not be sorted. 

## Using serializer

### JSON object serializer

It encodes and decodes all requests and responses using `json_encode()` and `json_decode()` (with `$assoc = false` flag) function from PHP standard library. This type of serializer is enabled by default. It is suitable for simple requests and responses (without any complex or nested data). Keep in mind that if you use classes for request objects they must be properly encoded by `json_encode()` function (you might have to use `JsonSerializable` interface).

```php
$client = $clientFactory->createClient('http://localhost:3000/rpc', [
    'serialization' => [
        'serializer_type' => 'object',
    ],
]);

// request parameters will be encoded by json_encode() function
$params = new \stdClass();
$params->subtrahend = 23;
$params->minuend = 42;

$result = $client->call('subtract', $params);

// $result will be an instance of stdClass
// $result->subtracted = 19;
```

### JSON array serializer

If you prefer to work with associative arrays you can use this type of serializer. It works exactly as JSON object serializer, but response payload will be returned as an associative array.

```php
$client = $clientFactory->createClient('http://localhost:3000/rpc', [
    'serialization' => [
        'serializer_type' => 'array',
    ],
]);

// request parameters will be encoded by json_encode() function
$params = [
    'subtrahend' => 23,
    'minuend' => 42,
];

$result = $client->call('subtract', $params);

// $result will be an associative array
// $result = ['subtracted' => 19];
```

### Symfony serializer

For handling complex and nested data in requests and responses you can use more complex serialization process provided by [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) component. First of all, you have to install additional components for working with classes.

```bash
composer require symfony/serializer symfony/property-info symfony/property-access
```

Also, you can install the whole serializer pack (recommended for use of this library as a Symfony Bundle). See more at [installation guide](https://symfony.com/doc/current/serializer.html#installation).

```bash
composer require symfony/serializer-pack
```

For working with such type of serializer you have to tell the client what kind of response classes to use for deserialization process. The deserialization process is conceived as every remote method has its own type of response class.

For example, you have a request to create a product.

```php
class CreateProductRequest
{
    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $productionDate;

    /** @var int */
    public $price;

    /** @var Image[] */
    public $images;
}
``` 

Also, you are expecting that any successful response will have the same structure.

```php
class CreateProductResponse
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $productionDate;

    /** @var int */
    public $price;

    /** @var Image[] */
    public $images;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $updatedAt;
}
```

If request data has some prohibited values, then server will return error with list of violations.

```php
class Violation
{
    /** @var string */
    public $propertyPath;

    /** @var string */
    public $message;
}
```

Now, we can create a client with keeping in mind all these prerequisites.

```php
$client = $clientFactory->createClient('http://localhost:3000/rpc', [
    'serialization' => [
        'serializer_type' => 'array',
        'result_types_by_methods' => [
            'createProduct' => CreateProductResponse::class,
        ],
        'default_error_type' => 'Violation[]',
    ],
]);

$newProduct = new CreateProductRequest();
$newProduct->name = 'Foo product';
$newProduct->productionDate = new DateTimeImmutable();
$newProduct->price = 1000;
$newProduct->images = [new Image('image.jpeg')];

try {
    $createdProduct = $client->call('createProduct', $newProduct);

    // $createdProduct is an instance of CreateProductResponse
} catch (\Strider2038\JsonRpcClient\Exception\ErrorResponseException $exception) {
    $violations = $exception->getError()->getData();
    // $violations is an array of instances of Violation class
}
```

If JSON RPC server has a specific error payload data for some methods, you can use serialization option `error_types_by_methods` to tune deserialized errors. In other cases you have to use capabilities of [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) component.

## Extending

There are three extensions points that you can use for your specific purposes. You can create your own id generator, serializer or transport. To create a client with you own implementations of these parts you have to use [ClientBuilder](./../src/ClientBuilder.php).

### Using builder for creating a client

Before using ClientBuilder you have to create transport. To create transport provided by this library you can use [MultiTransportFactory](./../src/Transport/MultiTransportFactory.php). Also, you can create your own transport by implementing [TransportInterface](./../src/Transport/TransportInterface.php).

```php
use Strider2038\JsonRpcClient\Transport\MultiTransportFactory;
use Strider2038\JsonRpcClient\Configuration\GeneralOptions;

$transportFactory = new MultiTransportFactory();
$generalOptions = GeneralOptions::createFromArray($options);
$transport = $this->transportFactory->createTransport($url, $generalOptions);
```

After that you can create a client using builder and setting your own implementations of [IdGeneratorInterface](./../src/Request/IdGeneratorInterface.php) and [MessageSerializerInterface](./../src/Serialization/MessageSerializerInterface.php).

```php
use Strider2038\JsonRpcClient\ClientBuilder;

$client = (new ClientBuilder($transport))
    ->setIdGenerator($idGeneratorImplementation)
    ->setSerializer($messageSerializerImplementation)
    ->getClient();
```

### Id generation

By default, JSON RPC client uses [SequentialIntegerIdGenerator](./../src/Request/SequentialIntegerIdGenerator.php) that simply uses an incremental id for all messages. Also, it will automatically use [UUIDv4](https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_(random)) if [ramsey/uuid](https://github.com/ramsey/uuid) library is installed.

To implement your own id generation algorithm use [IdGeneratorInterface](./../src/Request/IdGeneratorInterface.php).

### Serialization

You can build you own serialization process by implementing [MessageSerializerInterface](./../src/Serialization/MessageSerializerInterface.php). The most complex part of serializer is processing of batch requests. So you may need to use some context data of request. Context data `$context` is passed as a second argument to `deserialize()` method and contains:

* `$context['json_rpc']['result_types_by_methods']` set from [SerializationOptions](./../src/Configuration/SerializationOptions.php)
* `$context['json_rpc']['default_error_type']` set from [SerializationOptions](./../src/Configuration/SerializationOptions.php)
* `$context['json_rpc']['error_types_by_methods']` set from [SerializationOptions](./../src/Configuration/SerializationOptions.php)
* `$context['json_rpc']['request']` contains [RequestObjectInterface](./../src/Request/RequestObjectInterface.php) for singe request
* `$context['json_rpc']['requests']` contains an array of [RequestObjectInterface](./../src/Request/RequestObjectInterface.php) for batch request

### Transport

You can create your own implementation of transport using [TransportInterface](./../src/Transport/TransportInterface.php). Transport receives a serialized request as a string parameter and returns unserialized response as a string too. So, everything you need is to send some data via some interface and return response. If something goes wrong you have to throw an [RemoteProcedureCallFailedException](./../src/Exception/RemoteProcedureCallFailedException.php).

## Using PSR-18 transport

JSON RPC client library contains adapter for use with any [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client. Everything you need is to use [Psr18Transport](./../src/Transport/Http/Psr18Transport.php) and [ClientBuilder](./../src/ClientBuilder.php) to create a client.

```php
use Strider2038\JsonRpcClient\Transport\Http\Psr18Transport;
use Strider2038\JsonRpcClient\ClientBuilder;

$serverUri = 'http://localhost:8080/rpc';
$headers = [
   'Authorization' => 'Bearer secret_token',
];

$psr18transport = new Psr18Transport($psr18client, $serverUri, $headers);

$client = (new ClientBuilder($psr18transport))
     ->getClient();
```
