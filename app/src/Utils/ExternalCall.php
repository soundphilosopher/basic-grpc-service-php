<?php

declare(strict_types=1);

namespace App\Utils;

use GRPC\Basic\Service\V1\Proto\SomeServiceData;
use GRPC\Basic\Service\V1\Proto\SomeServiceResponse;

class ExternalCall
{
    public function call(string $name, string $version): SomeServiceResponse
    {
        $response = new SomeServiceResponse();
        $response->setId(\uniqid());
        $response->setName($name);
        $response->setVersion($version);

        $data = new SomeServiceData();
        $data->setType("test");
        $data->setValue("some value");

        $response->setData($data);
        return $response;
    }
}
