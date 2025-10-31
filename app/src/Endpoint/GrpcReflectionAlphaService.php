<?php

declare(strict_types=1);

namespace App\Endpoint;

use Grpc\Reflection\v1alpha\Proto\ServerReflectionInterface;
use Grpc\Reflection\v1alpha\Proto\ServerReflectionRequest;
use Grpc\Reflection\v1alpha\Proto\ServerReflectionResponse;
use Grpc\Reflection\v1alpha\Proto\FileDescriptorResponse;
use Grpc\Reflection\v1alpha\Proto\ExtensionNumberResponse;
use Grpc\Reflection\v1alpha\Proto\ListServiceResponse;
use Spiral\RoadRunner\GRPC;
use App\Utils\ServiceRegistry;

class GrpcReflectionAlphaService implements ServerReflectionInterface
{
    public function __construct(private ServiceRegistry $serviceRegistry) {}

    public function ServerReflectionInfo(
        GRPC\ContextInterface $ctx,
        ServerReflectionRequest $in,
    ): ServerReflectionResponse {
        $resp = new ServerReflectionResponse();

        if ($filename = $in->getFileByFilename()) {
            if (
                $file = $this->serviceRegistry->getFileByFilenameBytes(
                    $filename,
                )
            ) {
                $fileDescriptorResp = new FileDescriptorResponse([$file]);
                $resp->setFileDescriptorResponse($fileDescriptorResp);
            }
        }

        if ($symbols = $in->getFileContainingSymbol()) {
            if (
                $files = $this->serviceRegistry->getFileContainingSymbolBytes(
                    $symbols,
                )
            ) {
                $fileDescriptorResp = new FileDescriptorResponse($files);
                $resp->setFileDescriptorResponse($fileDescriptorResp);
            }
        }

        if ($extension = $in->getFileContainingExtension()) {
            if (
                $files = $this->serviceRegistry->getFileContainingExtensionBytes(
                    $extension->getContainingType(),
                    $extension->getExtensionNumber(),
                )
            ) {
                $fileDescriptorResp = new FileDescriptorResponse($files);
                $resp->setFileDescriptorResponse($fileDescriptorResp);
            }
        }

        if ($numbers = $in->getAllExtensionNumbersOfType()) {
            if (
                $extensions = $this->serviceRegistry->getAllExtensionNumbersOfType(
                    $numbers,
                )
            ) {
                $extensionNumberResp = new ExtensionNumberResponse($extensions);
                $resp->setAllExtensionNumbersResponse($extensionNumberResp);
            }
        }

        if ($in->getListServices()) {
            $services = $this->serviceRegistry->listServices();
            $listServiceResp = new ListServiceResponse($services);
            $resp->setListServicesResponse($listServiceResp);
        }

        return $resp;
    }
}
