# Walnut Lang Swoole HTTP Adapter
A small adapter for performing HTTP calls to Walnut language code using Swoole.

## Installation

To install the latest version, use the following command:

```bash
$ composer require kapitancho/walnut-lang-swoole-http-adapter
```

## Usage

Start a Swoole HTTP server using server.php or use the sample docker files 
and call http://localhost:8068/ in your browser.

```walnut-lang
module demo-swoole-http:

handleRequest = ^HttpRequest => HttpResponse :: [
   statusCode: 200,
   protocolVersion: HttpProtocolVersion.HTTP11,
   headers: [:],
   body: 'Hello world!'
];
```
