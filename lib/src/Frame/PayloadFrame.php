<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;
use JetBrains\PhpStorm\Pure;

class PayloadFrame extends Frame
{
    public function __construct(
        int $streamId,
        public readonly string $data,
        public readonly bool $follows = false,
        public readonly bool $complete = false,
        public readonly bool $next = true,
        public readonly ?string $metadata = null
    ) {
        parent::__construct($streamId);
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $toReturn = $buffer->toString();

        if ($this->metadata) {
            $metaDataSizeBuffer = new ArrayBuffer();
            $metaDataSizeBuffer->addUInt24(strlen($this->metadata));

            $toReturn .= sprintf('%s%s', $metaDataSizeBuffer->toString(), $this->metadata);
        }

        return sprintf('%s%s', $toReturn, $this->data ?? '');
    }

    private function generateTypeAndFlags(): int
    {
        $value = 10;
        $value = $value << 2;
        $value += $this->metadata ? 1 : 0;
        $value = $value << 1;
        $value += $this->follows ? 1 : 0;
        $value = $value << 1;
        $value += $this->complete ? 1 : 0;
        $value = $value << 1;
        $value += $this->next ? 1 : 0;

        $value = $value << 5;

        return $value;
    }

    #[Pure]
    public function getMetaData(): ?string
    {
        return $this->metadata;
    }

    #[Pure]
    public function getData(): string
    {
        return $this->data;
    }

    #[Pure]
    public function next(): bool
    {
        return $this->next;
    }

    #[Pure]
    public function complete(): bool
    {
        return $this->complete;
    }
}
