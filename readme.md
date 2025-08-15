# Basic gRPC Service written in PHP

## Prerequisites
- PHP 8.1 or higher
- Grpc PHP Extension
- Protobuf PHP Extension
- Composer
- buf.built CLI

## Limitations

Even we publish an gRPC server written in PHP here, it is not recommended for use.

> __Note__
> You can only create gRPC clients in PHP. Use another language to create a gRPC server.

The current problem is that the gRPC compiler cannot generate streaming methods, so all methods are unary.
That is the reason why we use unary methods instead of streaming methods and calls from grpcurl end up in a SEGFAULT from the roadrunner server or in out example tears down the worker pool.

## Installation

- Install dependencies using Composer: `composer install`
- Generate code and descriptor files using buf.built CLI: `composer run-script buf:generate`
- For security dump autoload classes: `composer dump-autoload`
