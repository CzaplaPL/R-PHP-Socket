<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final class ReasumeFrame extends Frame
{
    public function __construct(
        public readonly string $reasumeToken,
        public readonly int $receivedPosition,
        public readonly int $availablePosition,
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
        $buffer->addUInt32(0);
        $buffer->addUInt32($this->availablePosition);
        $toReturn = $buffer->toString();

        $reasumeTokenSize = new ArrayBuffer();
        $reasumeTokenSize->addUInt16(strlen($this->reasumeToken ?? ''));
        $toReturn .= $reasumeTokenSize->toString();
        $toReturn .= $this->reasumeToken ?? '';

        return $toReturn;
    }

    private function generateTypeAndFlags(): int
    {
        $value = 13;

        return $value << 10;
    }
}
