# HTTP Factories

Fyre provides PSR-17 factories for creating PSR-7 requests, responses, server requests, streams, uploaded files, and URIs. Type-hint the standard factory interfaces when application or third-party code should not depend on Fyre's concrete HTTP classes.

## Table of Contents

- [Start here](#start-here)
- [Available factories](#available-factories)
- [Creating HTTP objects](#creating-http-objects)
- [Server requests](#server-requests)
- [Related](#related)

## Start here

The default `Engine` binds every PSR-17 interface to its Fyre implementation. Resolve or inject only the interface needed by your component:

```php
use Psr\Http\Message\ResponseFactoryInterface;

$responseFactory = $app->use(ResponseFactoryInterface::class);
$response = $responseFactory->createResponse(202);
```

## Available factories

| PSR-17 interface | Fyre implementation |
| --- | --- |
| `RequestFactoryInterface` | `Fyre\Http\Factories\RequestFactory` |
| `ResponseFactoryInterface` | `Fyre\Http\Factories\ResponseFactory` |
| `ServerRequestFactoryInterface` | `Fyre\Http\Factories\ServerRequestFactory` |
| `StreamFactoryInterface` | `Fyre\Http\Factories\StreamFactory` |
| `UploadedFileFactoryInterface` | `Fyre\Http\Factories\UploadedFileFactory` |
| `UriFactoryInterface` | `Fyre\Http\Factories\UriFactory` |

The request, response, stream, uploaded-file, and URI factories have no constructor dependencies. The PSR-17 `ServerRequestFactory` instance uses the application `Config` and `TypeParser`, so it is normally resolved from the container.

## Creating HTTP objects

Factories can be injected into any container-built class:

```php
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ApiRequestBuilder
{
    public function __construct(
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory
    ) {}

    public function build(): \Psr\Http\Message\RequestInterface
    {
        return $this->requestFactory
            ->createRequest('POST', 'https://api.example.com/events')
            ->withBody($this->streamFactory->createStream('{"event":"signup"}'))
            ->withHeader('Content-Type', 'application/json');
    }
}
```

`StreamFactoryInterface` can create streams from strings, files, or existing readable stream resources.

The concrete request and response factories also accept Fyre constructor options through `RequestFactory::createFromOptions()` and `ResponseFactory::createFromOptions()`. The HTTP client uses the request factory when preparing outgoing requests. The `Engine` uses the response factory when resolving a new `ClientResponse`, including through the `response()` helper.

## Server requests

`ServerRequestFactoryInterface::createServerRequest()` uses the supplied method, URI, and server parameters without reading PHP superglobals. SAPI-style header fields in the server parameters are converted to request headers. As with other Fyre requests, the method is normalized to uppercase. The factory creates empty body, cookie, query, parsed-body, and uploaded-file state.

Use the regular `ServerRequest` service when handling the current PHP request. The `Engine` creates that service through `ServerRequestFactory::createFromGlobals()`:

```php
use Fyre\Http\ServerRequest;

$request = $app->use(ServerRequest::class);
```

For tests and other synthetic Fyre requests, resolve the concrete factory and call `createFromOptions()`. It accepts explicit request options without reading superglobals:

```php
use Fyre\Http\Factories\ServerRequestFactory;

$factory = $app->use(ServerRequestFactory::class);
$request = $factory->createFromOptions([
    'method' => 'POST',
    'uri' => 'https://example.com/events',
    'headers' => ['Content-Type' => 'application/json'],
    'body' => '{"event":"signup"}',
]);
```

`createFromOptions()` can also marshal raw `server` parameters and PHP `files` data. It derives the method, headers, and URI when they are not supplied explicitly and converts file data to PSR-7 uploaded files. Direct `ServerRequest` construction expects those values to already be normalized; its `server` option is stored as request metadata and does not populate other request fields.

The concrete URI and uploaded-file factories also provide marshalling helpers used by `ServerRequestFactory`:

- `UriFactory::createFromServer()` creates a URI from server parameters.
- `UploadedFileFactory::createUploadedFiles()` normalizes the nested `$_FILES` format into PSR-7 uploaded files.

## Related

- [HTTP Requests](requests.md)
- [HTTP Responses](responses.md)
- [URI](uri.md)
- [HTTP Client](client.md)
