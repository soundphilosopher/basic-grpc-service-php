<?php

declare(strict_types=1);

namespace App\Config;

use Spiral\Core\InjectableConfig;

final class RegistryConfig extends InjectableConfig
{
    public const string CONFIG = "registry";

    protected array $config = [
        "descriptor_path" => null,
    ];

    public function getDescriptorPath(): string
    {
        $path = (string) ($this->config["descriptor_path"] ?? "");
        if ($path === "") {
            throw new \RuntimeException(
                "grpc.descriptor_path is not configured.",
            );
        }
        return $path;
    }
}
