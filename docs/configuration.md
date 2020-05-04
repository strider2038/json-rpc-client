# Configuration

## Example

```php
$configuration = [
    'request_timeout_us'         => 200,
    'enable_response_processing' => false,
    'connection'                 => [
        'attempt_timeout_us' => 1,
        'timeout_multiplier' => 1.5,
        'max_attempts'       => 3,
    ],
    'http_client_type'        => 'guzzle',
    'transport_configuration' => [
        // any valid options for Guzzle client or Symfony Http Client
    ],
    'serialization'           => [
        'serializer_type'         => 'array',
        // these options has no effect without using Symfony Serializer
        'result_types_by_methods' => ['methodName' => ResposeClass::class],
        'default_error_type'      => ErrorClass::class,
        'error_types_by_methods'  => ['methodName' => SpecificErrorClass::class],
    ],
];

$client = $clientFactory->createClient('http://localhost:3000/rpc', $configuration);
```

## request_timeout_us

**type**: `integer` **default**: `1000000`

Request timeout in microseconds.

## enable_response_processing

**type**: `boolean` **default**: `true`

If enabled then all responses will be processed and client will return response payload. All responses for a batch request will be sorted accordingly to request order. If server returns error response, then instance of `Strider2038\JsonRpcClient\Exception\ErrorResponseException` will be thrown.

If disabled then client will return [ResponseObjectInterface](./../src/Response/ResponseObjectInterface.php) for each request or an array of [ResponseObjectInterface](./../src/Response/ResponseObjectInterface.php) for each batch request.

See [response processing](usage_guide.md#response-processing-and-error-handling) for more information and examples.

## http_client_type

**type**: `string` **default**: `autodetect`

This option can be one of following values

* `autodetect` automatically detects what type of client is installed (Guzzle or Symfony) and will try to create it
* `symfony` will force the creation of [Symfony Http Client](https://symfony.com/doc/current/components/http_client.html) (this value used as default when using library as a Symfony Bundle)
* `guzzle` will force the creation of [Guzzle client](https://github.com/guzzle/guzzle)

## transport_configuration

**type**: `array` **default**: `[]`

This option may contain any valid options for HTTP clients.

* See [Guzzle client options](http://docs.guzzlephp.org/en/stable/request-options.html)
* See [Symfony Http Client options](https://symfony.com/doc/current/reference/configuration/framework.html#reference-http-client)

## connection

Connection option is used only for Socket transport (TCP or Unix sockets).

### attempt_timeout_us

**type**: `integer` **default**: `100000`

Reconnection attempt timeout in microseconds. Must be greater than zero.

### timeout_multiplier

**type**: `float` **default**: `2.0`

Used to increase timeout value with growing reconnection attempts. Must be greater than 1.0.

Use `1.0` for linear scale:

* 1 attempt: timeout = 0 ms
* 2 attempt: timeout = 100 ms
* 3 attempt: timeout = 100 ms
* 4 attempt: timeout = 100 ms
* 5 attempt: timeout = 100 ms

Use `2.0` for quadratic scale:

* 1 attempt: timeout = 0 ms
* 2 attempt: timeout = 100 ms
* 3 attempt: timeout = 200 ms
* 4 attempt: timeout = 400 ms
* 5 attempt: timeout = 800 ms

### max_attempts

**type**: `integer` **default**: `5`

Max sequential attempts to reconnect with a remote server before fatal exception will be thrown. Must be greater than or equal to 1.

## serialization

Can be used to tune serialization process.

### serializer_type

**type**: `string` **default**: `object`

This option can be one of following values

* `object` uses [JSON object serializer](usage_guide.md#json-object-serializer)
* `array` uses [JSON array serializer](usage_guide.md#json-array-serializer)
* `symfony` uses [Symfony Serializer](usage_guide.md#symfony-serializer) (this value used as default when using library as a Symfony Bundle)

### result_types_by_methods

**type**: `array` **default**: `[]`

Used to deserialize successful server response to defined class or type.

```php
[
    'createProduct' => CreateProductResponse::class,
];
```

Works only with Symfony serializer.

### default_error_type

**type**: `string` **default**: `''`

Used to deserialize error data from server response to defined class or type. It can be used when all error data has the same structure or as fallback type for errors. If server can respond with specific error data on method you can use `error_types_by_methods` option.

### error_types_by_methods

**type**: `array` **default**: `[]`

Used to deserialize error data from server response after call to specific method.

```php
[
    'createProduct' => CreateProductErrors::class,
];
```
