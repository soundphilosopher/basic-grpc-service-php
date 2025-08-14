<?php

declare(strict_types=1);

/**
 * Configuration for gRPC.
 *
 * @link https://spiral.dev/docs/grpc-configuration#configuration
 */
return [
    /**
     * Path to protoc-gen-php-grpc library.
     * You can download the binary here: https://github.com/roadrunner-server/roadrunner/releases
     * Default: null
     */
    "binaryPath" =>
        directory("root") .
        "protoc-gen-php-grpc" .
        (\PHP_OS_FAMILY === "Windows" ? ".exe" : ""),

    /**
     * Path, where generated DTO files put.
     * Default: null
     */
    "generatedPath" => directory("root") . "/gen",

    /**
     * Base namespace for generated proto files.
     * Default: null
     */
    "namespace" => null,

    /**
     * Paths to proto files, that should be compiled into PHP by "grpc:generate" console command.
     */
    "services" => [
        directory("root") . "/proto/basic/v1/basic.proto",
        directory("root") . "/proto/basic/service/v1/service.proto",
        directory("root") . "/proto/io/cloudevents/v1/cloudevents.proto",
        directory("root") . "/proto/google/protobuf/descriptor.proto",
        directory("root") . "/proto/grpc/reflection/v1/reflection.proto",
        directory("root") . "/proto/grpc/reflection/v1alpha/reflection.proto",
    ],

    /**
     * Root path for all proto files in which imports will be searched.
     * Default: null
     */
    "servicesBasePath" => directory("root") . "/proto",
];
