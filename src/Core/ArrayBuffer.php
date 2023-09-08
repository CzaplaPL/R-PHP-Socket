<?php

namespace App\Core;

class ArrayBuffer
{
    /**
     * @var int[]
     */
    private  array $buffer;

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

    public function addUInt24(int $number) {
        $this->buffer[] = $number >> 16 & 0xFF;
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number  & 0xFF;
    }
    public function addUInt32(int $number) {
        $this->buffer[] = $number >> 24 & 0xFF;
        $this->buffer[] = $number >> 16 & 0xFF;
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number  & 0xFF;
    }

    public function addUInt16(int $number):void
    {
        $this->buffer[] = $number >> 8 & 0xFF;
        $this->buffer[] = $number  & 0xFF;
    }

    public function ToString(): string
    {
        $stringValue = '';

        foreach ($this->buffer as $byte) {
            $stringValue .= chr($byte);
        }

        return $stringValue;
    }

}