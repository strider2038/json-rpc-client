# JSON RPC v2 client for PHP

[![Latest Stable Version](https://poser.pugx.org/strider2038/json-rpc-client/v/stable)](https://packagist.org/packages/strider2038/json-rpc-client)
[![Total Downloads](https://poser.pugx.org/strider2038/json-rpc-client/downloads)](https://packagist.org/packages/strider2038/json-rpc-client)
[![License](https://poser.pugx.org/strider2038/json-rpc-client/license)](https://packagist.org/packages/strider2038/json-rpc-client)
[![Build Status](https://travis-ci.org/strider2038/json-rpc-client.svg?branch=master)](https://travis-ci.org/strider2038/json-rpc-client)
[![Build Status](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/build.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/strider2038/json-rpc-client/?branch=master)
[![StyleCI](https://github.styleci.io/repos/172254542/shield?branch=master)](https://github.styleci.io/repos/172254542)

Flexible JSON RPC v2 client for PHP written in object-oriented style.

* Works under HTTP protocol (via [Guzzle](https://github.com/guzzle/guzzle) or [Symfony Http Client](https://symfony.com/doc/current/components/http_client.html)) and TCP/Unix sockets (without any dependencies).
* Can be used with [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) to serialize requests and responses.
* Can be used as Symfony bundle.
* Can be used with any [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client.
* Implements reconnection algorithm for socket transport (useful for long-running processes).

## Installation

Use composer to install library. It is recommended to fix minor version while library is under development.

```bash
composer require strider2038/json-rpc-client ^0.4
```

Also, if you want to use it over HTTP protocol you have to install one of those clients: [Guzzle](https://github.com/guzzle/guzzle) or [Symfony Http Client](https://symfony.com/doc/current/components/http_client.html).

```bash
composer require guzzlehttp/guzzle
# or
composer require symfony/http-client
```

## How to use

* [Quick start](docs/quick_start.md)
* [Usage guide](docs/usage_guide.md)
  * [Response processing and error handling](docs/usage_guide.md#response-processing-and-error-handling)
  * [Using serializer](docs/usage_guide.md#using-serializer)
  * [Extending](docs/usage_guide.md#extending)
  * [Using PSR-18 transport](docs/usage_guide.md#using-psr-18-transport)
* [Using as Symfony Bundle](docs/symfony_bundle.md)
  * [Installation](docs/symfony_bundle.md#installation)
  * [Usage example](docs/symfony_bundle.md#usage-example)
  * [Configuration](docs/symfony_bundle.md#configuration)
  * [Using multiple clients](docs/symfony_bundle.md#using-multiple-clients)
* [Configuration](docs/configuration.md)

## Possible features for next releases 

* Object annotations for Symfony Bundle
* Symfony application example
* Caller context for possible authorization
* Bridge for JMS Serializer
* Web socket transport
