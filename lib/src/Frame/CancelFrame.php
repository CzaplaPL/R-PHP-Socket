<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;

class CancelFrame extends Frame
{
    public function __construct(
        int $streamId,
    ) {
        parent::__construct($streamId);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());

        return $buffer->toString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 9;

        return $value << 10;
    }
}
