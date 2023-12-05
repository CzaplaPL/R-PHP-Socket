<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;

final class KeepAliveFrame extends Frame
{
    public function __construct(
        private readonly bool $needResponse,
        private readonly string $data = '',
    ) {
        parent::__construct(Frame::SETUP_STREAM_ID);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt32(0);
        $buffer->addUInt32(0);

        return $buffer->toString().$this->data;
    }

    private function generateTypeAndFlags(): int
    {
        $value = 3;
        $value = $value << 3;
        $value += $this->needResponse ? 1 : 0;

        return $value << 7;
    }

    public function needResponse(): bool
    {
        return $this->needResponse;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
