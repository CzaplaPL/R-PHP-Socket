<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;
use App\Frame\Enums\ErrorType;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final class ErrorFrame extends Frame
{
    public function __construct(
        int $streamId,
        private readonly ErrorType $errorType,
        private readonly string $errorMesage = 'Error'
    ) {
        parent::__construct($streamId);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt32($this->errorType->value);

        return $buffer->toString().$this->errorMesage;
    }

    private function generateTypeAndFlags(): int
    {
        $value = 11;

        return $value << 10;
    }

    public function type(): ErrorType
    {
        return $this->errorType;
    }

    public function errorMesage(): string
    {
        return $this->errorMesage;
    }
}
