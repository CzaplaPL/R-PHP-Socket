<?php

declare(strict_types=1);

namespace App\Core;

class ArrayBuffer
{
    /**
     * @var int[]
     */
    private array $buffer;

    /**
     * @param int[] $buffer
     */
    public function __construct(array $buffer = [])
    {
        $this->buffer = $buffer;
    }

    /**
     * @return int[]
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    public function addUInt24(int $number): void
    {
        $this->buffer[] = $number >> 16 & 0xFF;
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number & 0xFF;
    }

    public function getUInt24(int $offset): int
    {
        $number = $this->buffer[$offset];
        $number = $number << 8;
        $number += $this->buffer[$offset + 1];
        $number = $number << 8;
        $number += $this->buffer[$offset + 2];

        return $number;
    }

    public function addUInt32(int $number): void
    {
        $this->buffer[] = $number >> 24 & 0xFF;
        $this->buffer[] = $number >> 16 & 0xFF;
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number & 0xFF;
    }

    public function getUInt32(int $offset): int
    {
        $number = $this->buffer[$offset];
        $number = $number << 8;
        $number += $this->buffer[$offset + 1];
        $number = $number << 8;
        $number += $this->buffer[$offset + 2];
        $number = $number << 8;
        $number += $this->buffer[$offset + 3];

        return $number;
    }

    public function addUInt16(int $number): void
    {
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number & 0xFF;
    }

    public function getUInt16(int $offset)
    {
        $number = $this->buffer[$offset];
        $number = $number << 8;
        $number += $this->buffer[$offset + 1];

        return $number;
    }

    public function toString(): string
    {
        $stringValue = '';

        foreach ($this->buffer as $byte) {
            $stringValue .= chr($byte);
        }

        return $stringValue;
    }
}
