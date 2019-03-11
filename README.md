# JSON RPC v2 client for PHP

Tiny JSON RPC v2 client for PHP

## Roadmap for v0.1

* [x] base client functions
  * [x] single request
  * [x] batch request
* [x] high level client / low level client
  * [x] high level client returns results and throws exceptions
  * [x] low level client returns results without validation
* [x] serializer interface
* [x] functional testing
* [x] response validator
* [x] add tcp transport support
* [x] add http transport support via guzzle
* [x] integration testing for tcp transport with server mock
* [ ] client factory
* [ ] logging transport wrapper
* [ ] travis ci testing
* [ ] basic how to use description

## Roadmap for v0.2

* [ ] client builder
* [ ] integration testing for http transport with server mock
* [ ] http authentication tests
* [ ] json array serializer
* [ ] bridge for symfony serializer
* [ ] bridge for jms serializer
* [ ] middleware interfaces
* [ ] add http transport support via psr-18
