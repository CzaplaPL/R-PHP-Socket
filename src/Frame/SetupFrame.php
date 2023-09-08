<?php

namespace App\Frame;

use App\Core\ArrayBuffer;

class SetupFrame implements IFame
{


    private int $streamId = 1;
    private int $hasMetadata = 0;
    private int $hasResumeEnable = 0;
    private int  $hasLease = 0;
    private int  $majorVersion = 1;
    private int  $minorVersion = 0;
    private int  $keepAlive = 500;
    private int  $maxLife = 2500;

    public function serialize(): string
    {

        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt16($this->majorVersion);
        $buffer->addUInt16($this->minorVersion);
        $buffer->addUInt32($this->keepAlive);
        $buffer->addUInt32($this->maxLife);
        $buffer->addUInt16(0);

        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(count($buffer->getBuffer()));
        return $sizeBuffer->ToString() . $buffer->ToString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 1;
        $value = $value << 2;
        $value += $this->hasMetadata ? 1 : 0 ;
        $value = $value << 1;
        $value += $this->hasResumeEnable ? 1 : 0 ;
        $value = $value << 1;
        $value += $this->hasLease ? 1 : 0 ;
        $value = $value << 6;

        return $value;
    }
}