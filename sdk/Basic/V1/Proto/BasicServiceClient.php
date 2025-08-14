<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Basic\V1\Proto;

/**
 */
class BasicServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Basic\Service\V1\Proto\HelloRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Basic\Service\V1\Proto\HelloResponse>
     */
    public function Hello(\Basic\Service\V1\Proto\HelloRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/basic.v1.BasicService/Hello',
        $argument,
        ['\Basic\Service\V1\Proto\HelloResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\BidiStreamingCall
     */
    public function Talk($metadata = [], $options = []) {
        return $this->_bidiRequest('/basic.v1.BasicService/Talk',
        ['\Basic\Service\V1\Proto\TalkResponse','decode'],
        $metadata, $options);
    }

    /**
     * @param \Basic\Service\V1\Proto\BackgroundRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function Background(\Basic\Service\V1\Proto\BackgroundRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/basic.v1.BasicService/Background',
        $argument,
        ['\Basic\Service\V1\Proto\BackgroundResponse', 'decode'],
        $metadata, $options);
    }

}
