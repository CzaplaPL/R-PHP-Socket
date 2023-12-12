<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;

class LeaseFrame extends Frame
{
    public function __construct(
        public readonly int $TTL,
        public readonly int $limit,
    ) {
        parent::__construct(self::SETUP_STREAM_ID);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt32($this->TTL);
        $buffer->addUInt32($this->limit);

        return $buffer->toString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 2;

        return $value << 10;
    }
}
