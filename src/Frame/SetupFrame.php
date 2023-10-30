<?php

declare(strict_types=1);

namespace App\Frame;

use App\Core\ArrayBuffer;
use App\Core\Exception\WrongConfigurationException;

final class SetupFrame extends Frame
{

    private const STREAM_ID = 0;

    /**
     * @throws WrongConfigurationException
     */
    public function __construct(
        private readonly int $keepAlive = 60000,
        private readonly int $lifetime = 300000,
        private readonly bool $reasumeEnable = false,
        private readonly bool $leaseEnable = false,
        private readonly ?string $reasumeToken = null,
        private readonly string $dataMimeType = "application/octet-stream",
        private readonly string $metadataMimeType = "application/octet-stream",
        private readonly ?string $metadata = null,
        private readonly ?string $data = null,
    )
    {
        parent::__construct(self::STREAM_ID);

        if($this->keepAlive <= 0){
            throw WrongConfigurationException::wrongKeepAlive();
        }

        if($this->lifetime <= 0){
            throw WrongConfigurationException::wrongLifetime();
        }

    }


    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt16($this->majorVersion);
        $buffer->addUInt16($this->minorVersion);
        $buffer->addUInt32($this->keepAlive);
        $buffer->addUInt32($this->lifetime);
        $buffer->addUInt16(0);

        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(count($buffer->getBuffer()));

        return $sizeBuffer->ToString().$buffer->ToString();
    }

    private function generateTypeAndFlags(): int
    {
        $value = 1;
        $value = $value << 2;
        $value += $this->metadata ? 1 : 0;
        $value = $value << 1;
        $value += $this->reasumeEnable ? 1 : 0;
        $value = $value << 1;
        $value += $this->leaseEnable ? 1 : 0;
        $value = $value << 6;

        return $value;
    }

    public function payload(): string
    {
        return '';
    }
}
