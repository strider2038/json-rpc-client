# JSON RPC v2 client for PHP

Tiny JSON RPC v2 client for PHP

## Roadmap

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
* [ ] add http transport support via guzzle
* [x] integration testing for tcp transport with server mock
* [ ] integration testing for http transport with server mock
* [ ] json array serializer
* [ ] client builder
* [ ] middleware interfaces
* [ ] logging?
* [ ] bridge for symfony serializer
* [ ] bridge for jms serializer
