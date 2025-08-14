<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Config\RegistryConfig;
use App\Utils\ServiceRegistry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;

final class ServiceRegistryBootloader extends Bootloader
{
    protected const array SINGLETONS = [
        ServiceRegistry::class => [self::class, "initServiceRegistry"],
    ];

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(
        private readonly ConfiguratorInterface $config,
    ) {}

    public function boot(DirectoriesInterface $dirs): void
    {
        $this->config->setDefaults(RegistryConfig::CONFIG, [
            "descriptor_path" => $dirs->get("root") . "sdk/descriptor.bin",
        ]);
    }

    private function initServiceRegistry(RegistryConfig $cfg): ServiceRegistry
    {
        return new ServiceRegistry($cfg->getDescriptorPath());
    }
}
