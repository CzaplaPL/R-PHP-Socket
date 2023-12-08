<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;
use JetBrains\PhpStorm\Pure;

class RequestNFrame extends Frame
{
    public function __construct(
        int $streamId,
        public readonly int $requestN,
    ) {
        parent::__construct($streamId);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt32($this->requestN);

        return $buffer->toString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 8;

        return $value << 10;
    }

    #[Pure]
    public function getRequestN(): int
    {
        return $this->requestN;
    }
}
