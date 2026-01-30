# 🚀 Basic gRPC Service in PHP

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-787CB5?style=for-the-badge&logo=php&logoColor=white)
![gRPC](https://img.shields.io/badge/gRPC-00ADD8?style=for-the-badge&logo=grpc&logoColor=white)
![Protocol Buffers](https://img.shields.io/badge/Protobuf-336791?style=for-the-badge&logo=protocol-buffers&logoColor=white)
![CloudEvents](https://img.shields.io/badge/CloudEvents-0078D4?style=for-the-badge&logo=icloud&logoColor=white)

[![Spiral Framework](https://img.shields.io/badge/spiral-framework-blue)](https://spiral.dev)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Buf](https://img.shields.io/badge/buf-build-orange)](https://buf.build)

*A demonstration gRPC service built with PHP using the Spiral Framework and RoadRunner, showcasing unary RPC methods with CloudEvents integration. ✨*

## 📋 Overview

This project demonstrates a basic gRPC service implementation in PHP, featuring:
- 🎯 **Unary RPC methods** (Hello, Talk, Background processing)
- ☁️ **CloudEvents integration** for event-driven responses
- 🛠️ **Buf CLI integration** for protocol buffer code generation
- 🔐 **TLS/SSL support** with certificate-based encryption
- ⚡ **Asynchronous background processing** with Amphp
- 🔍 **Reflection support** for service discovery
- 📝 **Structured logging** with Monolog

## ⚠️ Important Limitations

**PHP gRPC Streaming Limitations**: The current PHP protobuf compiler **does not support streaming methods**. While the `.proto` files define streaming RPCs (`Talk` and `Background`), they are implemented as **unary methods** due to this limitation. This is a known constraint when using gRPC with PHP. 🚧

## 🏗️ Architecture

```basic-grpc-service-php/app/src/Endpoint/BasicService.php#L1-20
<?php

declare(strict_types=1);

namespace App\Endpoint;

use App\Utils\ExternalCall;
use App\Utils\Talk;
use Google\Protobuf\Timestamp;
use Basic\Service\V1\Proto\BackgroundResponseEvent;
use Google\Protobuf\Any;
use Basic\Service\V1\Proto\BackgroundRequest;
use Basic\Service\V1\Proto\BackgroundResponse;
use Basic\Service\V1\Proto\HelloRequest;
use Basic\Service\V1\Proto\HelloResponse;
use Basic\Service\V1\Proto\HelloResponseEvent;
use Basic\Service\V1\Proto\State;
use Basic\Service\V1\Proto\TalkRequest;
use Basic\Service\V1\Proto\TalkResponse;
use Basic\V1\Proto\BasicServiceInterface;
```

### 🎪 Service Definition

The service implements three main RPC methods:

```basic-grpc-service-php/proto/basic/v1/basic.proto#L1-12
syntax = "proto3";

package basic.v1;

option php_namespace = "Basic\\V1\\Proto";
option php_metadata_namespace = "Basic\\V1\\GPBMetadata";

import "basic/service/v1/service.proto";

service BasicService {
    rpc Hello(basic.service.v1.HelloRequest) returns (basic.service.v1.HelloResponse) {}
    rpc Talk(stream basic.service.v1.TalkRequest) returns (stream basic.service.v1.TalkResponse) {}
    rpc Background(basic.service.v1.BackgroundRequest) returns (stream basic.service.v1.BackgroundResponse) {}
}
```

## 📦 Prerequisites

- 🐘 **PHP 8.1 or higher**
- 🔌 **gRPC PHP Extension** (`ext-grpc`)
- 📡 **Protobuf PHP Extension** (`ext-protobuf`)
- 🎼 **Composer** for dependency management
- 🦬 **Buf CLI** for protocol buffer generation
- 🛣️ **RoadRunner** binary (automatically downloaded)

## 🚀 Installation

1. **Clone the repository** 📥:
   ```bash
   git clone https://github.com/soundphilosopher/basic-grpc-service-php
   cd basic-grpc-service-php
   ```

2. **Install PHP dependencies** 📚:
   ```bash
   composer install
   ```

3. **Generate protocol buffer code** ⚙️:
   ```bash
   composer run-script buf:generate
   ```

4. **Download RoadRunner binary** 🏃‍♂️:
   ```bash
   composer run-script rr:download
   ```

5. **Generate TLS certificates** 🔒 (for HTTPS/gRPC over TLS):
   ```bash
   # Create self-signed certificates for development
   mkcert -install
   mkcert -cert-file ./certs/local.crt -key-file ./certs/local.key localhost 127.0.0.1 0.0.0.0 ::1
   ```

## ⚙️ Configuration

### 🛣️ RoadRunner Configuration

```basic-grpc-service-php/.rr.yaml#L1-15
version: "3"

logs:
  encoding: json

rpc:
  listen: "tcp://127.0.0.1:6001"

grpc:
  listen: "tcp://127.0.0.1:9443"
  tls:
    key: "certs/local.key"
    cert: "certs/local.crt"
  proto:
    - "proto/basic/v1/basic.proto"
    - "proto/basic/service/v1/service.proto"
```

### 🦬 Buf Configuration

```basic-grpc-service-php/buf.gen.yaml#L1-10
version: v2
plugins:
  # generate PHP code/procedures
  - remote: buf.build/community/roadrunner-server-php-grpc:v5.0.2
    out: sdk
  - remote: buf.build/grpc/php:v1.74.0
    out: sdk
  - remote: buf.build/protocolbuffers/php:v31.1
    out: sdk
```

## 🎬 Running the Service

1. **Start the gRPC server** 🎯:
   ```bash
   ./rr serve
   ```

2. **The service will be available at** 🌐:
   - gRPC endpoint: `localhost:9443` (TLS) 🔐
   - RPC endpoint: `localhost:6001` 🚪

## 📖 API Reference

### 👋 Hello Method
- **Input**: `HelloRequest` with `message` field
- **Output**: `HelloResponse` with CloudEvent containing greeting
- **Purpose**: Simple greeting service that returns a CloudEvent response

### 💬 Talk Method
- **Input**: `TalkRequest` with `message` field
- **Output**: `TalkResponse` with conversational reply
- **Purpose**: ELIZA-style chatbot that provides therapeutic responses 🤖

### ⚡ Background Method
- **Input**: `BackgroundRequest` with `processes` count
- **Output**: `BackgroundResponse` with CloudEvent containing processing results
- **Purpose**: Simulates async background processing with multiple concurrent tasks

## 🧪 Testing

### 🎯 Unit Tests

```bash
# Run all tests
composer test 🚀

# Run tests with coverage
composer test-coverage 📊

# Static analysis
composer psalm 🔍
```

## 👩‍💻 Development Workflow

### 🔄 Code Generation

```bash
# Regenerate protocol buffer code
composer run-script buf:generate 🦬

# Fix code style
composer run-script cs:fix ✨

# Run static analysis
composer psalm 🔍
```

### 📁 Project Structure

```
basic-grpc-service-php/
├── app/ 📁                    # Application source code
│   ├── config/ ⚙️            # Configuration files
│   └── src/ 💻               # PHP source files
│       ├── Application/ 🏗️   # Spiral application setup
│       ├── Endpoint/ 🎯      # gRPC service implementations
│       └── Utils/ 🛠️         # Utility classes
├── certs/ 🔐                 # TLS certificates
├── proto/ 📡                 # Protocol buffer definitions
├── sdk/ 📦                   # Generated PHP code from proto files
├── tests/ 🧪                 # Test files
├── buf.gen.yaml 🦬          # Buf code generation config
├── buf.yaml 📋              # Buf workspace config
├── .rr.yaml 🛣️              # RoadRunner configuration
└── composer.json 📚         # PHP dependencies
```

## 🌟 Key Features

### ☁️ CloudEvents Integration
All responses are wrapped in CloudEvents format, providing standardized event metadata:

```basic-grpc-service-php/app/src/Endpoint/BasicService.php#L35-45
        $protoData = new Any();
        $protoData->pack($event);

        $cloudevent = new CloudEvent();
        $cloudevent->setId(\uniqid());
        $cloudevent->setSource("basic/v1/hello");
        $cloudevent->setType("greeting");
        $cloudevent->setSpecVersion("1.0");
        $cloudevent->setProtoData($protoData);
```

### ⚡ Asynchronous Processing
The Background method demonstrates concurrent processing using Amphp:

```basic-grpc-service-php/app/src/Endpoint/BasicService.php#L71-81
        for ($i = 0; $i < $in->getProcesses(); $i++) {
            $features[$i] = async(function () use ($externalService, $i) {
                delay(random_int(1, 3));
                return $externalService->call("service-{$i}", "0.{$i}.1");
            });
        }

        while ($features) {
            $response = Future\awaitAny($features);
            $responses = $event->getResponses();
```

### 🤖 ELIZA Chatbot
The Talk utility implements a classic ELIZA chatbot with pattern matching:

```basic-grpc-service-php/app/src/Utils/Talk.php#L50-60
    private static array $requestInputRegexToResponseOptions = [
        "/i need (.*)/" => [
            "Why do you need %s?",
            "Would it really help you to get %s?",
            "Are you sure you need %s?",
        ],
        '/why don\'?t you ([^\?]*)\??/' => [
            "Do you really think I don't %s?",
            "Perhaps eventually I will %s.",
```

## 🚨 Known Issues & Limitations

1. **🚧 Streaming Not Supported**: PHP's protobuf compiler doesn't generate streaming method implementations
2. **💥 grpcurl Compatibility**: Some grpcurl operations may cause segfaults due to streaming method definitions
3. **🏭 Production Readiness**: This is a demonstration project; additional hardening needed for production use

## 🤝 Contributing

1. Follow PSR-12 coding standards 📏
2. Run code style fixer: `composer run-script cs:fix` ✨
3. Ensure all tests pass: `composer test` ✅
4. Update protocol buffers if needed: `composer run-script buf:generate` 🔄

## 📚 Dependencies

- 🌀 **Spiral Framework**: Modern PHP framework with dependency injection
- 🛣️ **RoadRunner**: High-performance application server for PHP
- 📡 **gRPC PHP**: Official gRPC extension for PHP
- ⚡ **Amphp**: Async concurrency framework
- 🦬 **Buf**: Modern protobuf toolchain

## 📄 License

MIT License - see [LICENSE](./LICENSE) file for details 📋

---

**Note**: This project serves as an educational example of implementing gRPC services in PHP. While functional, consider the streaming limitations when building production services. 🎓✨
