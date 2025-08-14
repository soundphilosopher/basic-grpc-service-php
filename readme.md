# Basic gRPC Service written in PHP

## Prerequisites
- PHP 8.1 or higher
- Grpc PHP Extension
- Protobuf PHP Extension
- Composer
- buf.built CLI

## Installation

- Install dependencies using Composer: `composer install`
- Generate code and descriptor files using buf.built CLI: `composer run-script buf:generate`
- For security dump autoload classes: `composer dump-autoload`
