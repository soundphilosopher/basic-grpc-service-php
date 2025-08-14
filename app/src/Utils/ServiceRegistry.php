<?php

declare(strict_types=1);

namespace App\Utils;

use Google\Protobuf\Proto\DescriptorProto;
use Google\Protobuf\Proto\FieldDescriptorProto;
use Google\Protobuf\Proto\FileDescriptorProto;
use Google\Protobuf\Proto\FileDescriptorSet;
use Google\Protobuf\Proto\ServiceDescriptorProto;
use Grpc\Reflection\V1\Proto\FileDescriptorResponse;

final class ServiceRegistry
{
    private FileDescriptorSet $descriptorSet;

    /** @var array<string, ServiceDescriptorProto> fq service -> descriptor */
    private array $services = [];

    /** @var array<string, FileDescriptorProto> filename -> file proto */
    private array $filesByName = [];

    /** @var array<string, FileDescriptorProto> fully-qualified symbol -> defining file proto */
    private array $symbolToFile = [];

    /** @var array<string, array<int, FileDescriptorProto>> extendee fq msg -> (tag -> file) */
    private array $extensionsByExtendee = [];

    public function __construct(string $descriptorPath)
    {
        if (!is_file($descriptorPath)) {
            throw new \RuntimeException(
                "Descriptor file not found: {$descriptorPath}",
            );
        }
        $bytes = file_get_contents($descriptorPath);
        if ($bytes === false) {
            throw new \RuntimeException(
                "Failed to read descriptor file: {$descriptorPath}",
            );
        }

        $fds = new FileDescriptorSet();
        $fds->mergeFromString($bytes);
        $this->descriptorSet = $fds;

        /** @var FileDescriptorProto $file */
        foreach ($fds->getFile() as $file) {
            $this->filesByName[$file->getName()] = $file;

            $pkg = $file->getPackage();
            $this->indexFileSymbols($file, $pkg);

            /** @var ServiceDescriptorProto $svc */
            foreach ($file->getService() as $svc) {
                $fqService =
                    $pkg !== "" ? "{$pkg}.{$svc->getName()}" : $svc->getName();
                $this->services[$fqService] = $svc;
            }
        }
    }

    // ----------------------
    //  Byte-list helpers
    // ----------------------

    /**
     * Return serialized FileDescriptorProto bytes for $filename and all its transitive dependencies.
     * @return string[]|null
     */
    public function getFileByFilenameBytes(string $filename): ?array
    {
        $file = $this->filesByName[$filename] ?? null;
        if (!$file) {
            return null;
        }
        $files = $this->collectFileWithDeps($file);
        return array_map(
            static fn(FileDescriptorProto $f) => $f->serializeToString(),
            $files,
        );
    }

    /**
     * Like the TS getFileContainingSymbol(): find the defining file for $fqSymbol and return it + deps.
     * @return string[]|null
     */
    public function getFileContainingSymbolBytes(string $fqSymbol): ?array
    {
        $file = $this->symbolToFile[$fqSymbol] ?? null;
        if (!$file) {
            return null;
        }
        $files = $this->collectFileWithDeps($file);
        return array_map(
            static fn(FileDescriptorProto $f) => $f->serializeToString(),
            $files,
        );
    }

    /**
     * Like the TS getFileContainingExtension(): find the file that defines an extension for $containingType/$number.
     * @return string[]|null
     */
    public function getFileContainingExtensionBytes(
        string $containingType,
        int $number,
    ): ?array {
        $byNum = $this->extensionsByExtendee[$containingType] ?? null;
        if (!$byNum) {
            return null;
        }
        $file = $byNum[$number] ?? null;
        if (!$file) {
            return null;
        }
        $files = $this->collectFileWithDeps($file);
        return array_map(
            static fn(FileDescriptorProto $f) => $f->serializeToString(),
            $files,
        );
    }

    /**
     * getAllExtensionNumbersOfType(registry, value) -> int[]
     * - $value is a fully-qualified message name (<package>.<Message>[.<Nested>...])
     */
    public function getAllExtensionNumbersOfType(string $fqMessage): array
    {
        $byNum = $this->extensionsByExtendee[$fqMessage] ?? [];
        // keys are numbers, values are files; return the numbers as an unsorted list
        return array_values(array_keys($byNum));
    }

    // ----------------------
    //  Ready-made Response builders
    // ----------------------

    public function fileByFilenameResponse(
        string $filename,
    ): ?FileDescriptorResponse {
        $bytes = $this->getFileByFilenameBytes($filename);
        return $bytes !== null
            ? new FileDescriptorResponse()->setFileDescriptorProto($bytes)
            : null;
    }

    public function fileContainingSymbolResponse(
        string $fqSymbol,
    ): ?FileDescriptorResponse {
        $bytes = $this->getFileContainingSymbolBytes($fqSymbol);
        return $bytes !== null
            ? new FileDescriptorResponse()->setFileDescriptorProto($bytes)
            : null;
    }

    public function fileContainingExtensionResponse(
        string $containingType,
        int $number,
    ): ?FileDescriptorResponse {
        $bytes = $this->getFileContainingExtensionBytes(
            $containingType,
            $number,
        );
        return $bytes !== null
            ? new FileDescriptorResponse()->setFileDescriptorProto($bytes)
            : null;
    }

    // ----------------------
    //  Existing APIs
    // ----------------------

    public function findService(string $name): ?ServiceDescriptorProto
    {
        return $this->services[$name] ?? null;
    }

    public function getDescriptorSet(): FileDescriptorSet
    {
        return $this->descriptorSet;
    }

    /** @return string[] */
    public function listServices(): array
    {
        return array_keys($this->services);
    }

    // ----------------------
    //  Indexing & utilities
    // ----------------------

    private function indexFileSymbols(
        FileDescriptorProto $file,
        string $pkg,
    ): void {
        foreach ($file->getMessageType() as $msg) {
            $this->indexMessage(
                $file,
                $pkg,
                $msg,
                $this->qualify($pkg, $msg->getName()),
            );
        }
        foreach ($file->getEnumType() as $enum) {
            $this->symbolToFile[$this->qualify($pkg, $enum->getName())] = $file;
        }
        foreach ($file->getExtension() as $ext) {
            $this->indexExtension($file, $pkg, $ext);
        }
        foreach ($file->getService() as $svc) {
            $fqService = $this->qualify($pkg, $svc->getName());
            $this->symbolToFile[$fqService] = $file;
            foreach ($svc->getMethod() as $m) {
                $this->symbolToFile[$fqService . "." . $m->getName()] = $file;
            }
        }
    }

    private function indexMessage(
        FileDescriptorProto $file,
        string $pkg,
        DescriptorProto $msg,
        string $fqMessage,
    ): void {
        $this->symbolToFile[$fqMessage] = $file;

        foreach ($msg->getNestedType() as $nested) {
            $this->indexMessage(
                $file,
                $pkg,
                $nested,
                $fqMessage . "." . $nested->getName(),
            );
        }
        foreach ($msg->getEnumType() as $enum) {
            $this->symbolToFile[$fqMessage . "." . $enum->getName()] = $file;
        }
        foreach ($msg->getExtension() as $ext) {
            $this->indexExtension($file, $pkg, $ext);
        }
    }

    private function indexExtension(
        FileDescriptorProto $file,
        string $pkg,
        FieldDescriptorProto $ext,
    ): void {
        $extendee = ltrim($ext->getExtendee(), ".");
        $number = $ext->getNumber();
        if ($extendee !== "" && $number !== null) {
            $this->extensionsByExtendee[$extendee][$number] = $file;
        }

        // Optional: map "<pkg>.<extName>" to the file as a convenience.
        $maybeSymbol = $this->qualify($pkg, $ext->getName());
        if ($maybeSymbol !== "") {
            $this->symbolToFile[$maybeSymbol] = $file;
        }
    }

    /**
     * Depth-first collect the file and all transitive dependencies (de-duplicated).
     * Order isn’t mandated by the reflection spec; this returns a postorder list where
     * dependencies appear before the file that depends on them.
     *
     * @return FileDescriptorProto[]
     */
    private function collectFileWithDeps(FileDescriptorProto $root): array
    {
        $result = [];
        $seen = [];
        $stack = [$root];

        // We’ll do an explicit DFS to preserve a dependency-first order.
        $visit = function (FileDescriptorProto $file) use (
            &$visit,
            &$result,
            &$seen,
        ) {
            $name = $file->getName();
            if (isset($seen[$name])) {
                return;
            }
            $seen[$name] = true;

            // Recurse on dependencies first
            foreach ($file->getDependency() as $depName) {
                if (isset($this->filesByName[$depName])) {
                    $visit($this->filesByName[$depName]);
                }
            }

            // Then include this file
            $result[] = $file;
        };

        $visit($root);
        return $result;
    }

    private function qualify(string $pkg, string $name): string
    {
        return $pkg !== "" ? "{$pkg}.{$name}" : $name;
    }
}
