<?php

namespace App\Frame;

use App\Core\ArrayBuffer;

class RequestResponseFrame implements IFrame
{
    private int $streamId;
    private string $payload;
    private int $hasMetadata = 0;

    /**
     * @param int $streamId
     */
    public function __construct(int $streamId, string $payload)
    {
        $this->streamId = $streamId;
        $this->payload = $payload;
    }


    public function serialize(): string
    {

        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());


        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(count($buffer->getBuffer()) + strlen($this->payload));
        return $sizeBuffer->ToString() . $buffer->ToString() . $this->payload;
    }

    private function generateTypeAndFlags(): int
    {
        $value = 4;
        $value = $value << 2;
        $value += $this->hasMetadata ? 1 : 0 ;
        return $value << 8;
    }

    public function streamId(): int
    {
        return $this->streamId;
    }

    public function complete(): bool
    {
        return true;
    }

    public function next(): bool
    {
        return true;
    }

    public function payload(): string
    {
       return $this->payload;
    }
}