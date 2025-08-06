<?php

declare(strict_types=1);

namespace App\Endpoint;

use App\Utils\ExternalCall;
use App\Utils\Talk;
use Google\Protobuf\Timestamp;
use GRPC\Basic\Service\V1\Proto\BackgroundResponseEvent;
use Google\Protobuf\Any;
use GRPC\Basic\Service\V1\Proto\BackgroundRequest;
use GRPC\Basic\Service\V1\Proto\BackgroundResponse;
use GRPC\Basic\Service\V1\Proto\HelloRequest;
use GRPC\Basic\Service\V1\Proto\HelloResponse;
use GRPC\Basic\Service\V1\Proto\HelloResponseEvent;
use GRPC\Basic\Service\V1\Proto\State;
use GRPC\Basic\Service\V1\Proto\TalkRequest;
use GRPC\Basic\Service\V1\Proto\TalkResponse;
use GRPC\Basic\V1\Proto\BasicServiceInterface;
use GRPC\Io\CloudEvents\V1\Proto\CloudEvent;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\GRPC;
use Amp\Future;
use function Amp\async;
use function Amp\delay;

class BasicService implements BasicServiceInterface
{
    public function __construct(private LoggerInterface $log) {}

    public function Hello(
        GRPC\ContextInterface $ctx,
        HelloRequest $in,
    ): HelloResponse {
        $event = new HelloResponseEvent();
        $event->setGreeting("Hello, {$in->getMessage()}!");

        $protoData = new Any();
        $protoData->pack($event);

        $cloudevent = new CloudEvent();
        $cloudevent->setId(\uniqid());
        $cloudevent->setSource("basic/v1/hello");
        $cloudevent->setType("greeting");
        $cloudevent->setSpecVersion("1.0");
        $cloudevent->setProtoData($protoData);

        $response = new HelloResponse();
        $response->setCloudEvent($cloudevent);

        return $response;
    }

    public function Talk(
        GRPC\ContextInterface $ctx,
        TalkRequest $in,
    ): TalkResponse {
        $answer = Talk::reply($in->getMessage());

        $response = new TalkResponse();
        $response->setAnswer($answer);

        return $response;
    }

    public function Background(
        GRPC\ContextInterface $ctx,
        BackgroundRequest $in,
    ): BackgroundResponse {
        $externalService = new ExternalCall();
        $features = [];

        $startedAt = new Timestamp();
        $startedAt->fromDateTime(new \DateTime());
        $event = new BackgroundResponseEvent();
        $event->setState(State::PROCESS_STATE_PROCESS);
        $event->setStartedAt($startedAt);

        for ($i = 0; $i < $in->getProcesses(); $i++) {
            $features[$i] = async(function () use ($externalService, $i) {
                delay(random_int(1, 3));
                return $externalService->call("service-{$i}", "0.{$i}.1");
            });
        }

        while ($features) {
            $response = Future\awaitAny($features);
            $event->getResponses()[] = $response;

            // error_log("Event: {$event->serializeToJsonString()}");
            $this->log->info("Event: {$event->serializeToJsonString()}");

            foreach ($features as $key => $feature) {
                if ($feature->isComplete()) {
                    unset($features[$key]);
                }
            }
        }

        $completedAt = new Timestamp();
        $completedAt->fromDateTime(new \DateTime());
        $event->setState(State::PROCESS_STATE_COMPLETE);
        $event->setCompletedAt($completedAt);

        $any = new Any();
        $any->pack($event);

        $cloudevent = new CloudEvent();
        $cloudevent->setId(\uniqid());
        $cloudevent->setSource("basic/v1/background");
        $cloudevent->setType("processes");
        $cloudevent->setSpecVersion("1.0");
        $cloudevent->setProtoData($any);

        $response = new BackgroundResponse();
        $response->setCloudEvent($cloudevent);

        return $response;
    }
}
