# Using as Symfony Bundle

- [Using as Symfony Bundle](#using-as-symfony-bundle)

## Installation

This library is compatible with Symfony 4 and 5. It is recommended to use [Symfony Http Client](https://symfony.com/doc/current/components/http_client.html) and [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) components alongside. Alternatevily you can use [Guzzle](https://github.com/guzzle/guzzle) client.

Add lines to `config/bundles.php` (no automatic configuration is available at the moment).

```php
<?php

return [
    // ...
    Strider2038\JsonRpcClient\Bridge\Symfony\JsonRpcClientBundle::class => ['all' => true],
];

```

Create package config file `config/packages/json_rpc_client.yaml` with your configuration. Example of configuration file with one client.

```yaml
json_rpc_client:
  default:
    url: '%env(JSON_RPC_SERVER_URL)%'
    options:
      transport_configuration:
        headers:
          Authorization: 'Bearer %env(BEARER_TOKEN)%'
```

## Usage example

If you have client with name `default` in a configuration (as in previous example) you can simply inject `Strider2038\JsonRpcClient\ClientInterface` into your services (if autowiring is enabled) and use it on your own.

```php
use Strider2038\JsonRpcClient\ClientInterface;

class ClientService {
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client) {
        $this->client = $client;
    }

    public function doRemoteCalculation(CalculationParameters $parameters): CalculationResult
    {
        return $this->client->call('calculate', $parameters);
    }
}
```

## Configuration

Almost all options are equal to ones described in [configuration](configuration.md).

```yaml
json_rpc_client:
  default: # you can use any number of clients, but only default client has automatic alias for ClientInterface
    url: '%env(JSON_RPC_SERVER_URL)%' # 
    options:
      request_timeout_us: 1000000
      connection:
        attempt_timeout_us: 100000
        timeout_multiplier: 2.0
        max_attempts: 5
      # by default `symfony` option is used to create Symfony Http Client instance
      # you can use `guzzle` to create Guzzle Client instance
      http_client_type: 'autodetect'
      transport_configuration:
        # guzzle / symfony http client options
      serialization:
        # by default, instance of Symfony Serializer injected into JSON RPC client
        # also you can use 'object' or 'array' serializers for simple cases
        serializer_type: 'symfony'
        # this map is used to deserialize response to specific classes 
        result_types_by_methods:
          createProduct: CreateProductResponse
        # this is default type to deserialize error payload
        default_error_type: Violation[]
        # or you can use specific error classes by server methods
        error_types_by_methods:
          method: error_type
```

## Using multiple clients

You may define a multiple clients in your config.

```yaml
json_rpc_client:
  calculator:
    url: 'http://calculation_server/rpc'
  logger:
    url: 'http://logging_server/rpc'
```

To use them you have to manually inject them into your services. 

```yaml
services:
  calculation_service:
    class: App\CalculationService
     arguments:
      $client: 'json_rpc_client.calculator'

  logging_service:
    class: App\LoggingService
    arguments:
      $client: 'json_rpc_client.logger'
```
