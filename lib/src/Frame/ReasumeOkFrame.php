<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final class ReasumeOkFrame extends Frame
{
    public function __construct(
        public readonly int $receivedPosition,
    ) {
        parent::__construct(self::SETUP_STREAM_ID);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt16(self::MAJOR_VERSION);
        $buffer->addUInt16(self::MINOR_VERSION);
        $buffer->addUInt32(0);
        $buffer->addUInt32($this->receivedPosition);

        return $buffer->toString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 13;

        return $value << 10;
    }
}
