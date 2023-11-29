<?php

declare(strict_types=1);

namespace App\Frame;

use App\Connection\Client\ConnectionSettings;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\WrongConfigurationException;
use JetBrains\PhpStorm\Pure;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final class SetupFrame extends Frame
{
    public const SETUP_STREAM_ID = 0;

    public function __construct(
        public readonly int $keepAlive = 60000,
        public readonly int $lifetime = 300000,
        public readonly bool $reasumeEnable = false,
        public readonly bool $leaseEnable = false,
        public readonly ?string $reasumeToken = null,
        public readonly string $dataMimeType = 'application/octet-stream',
        public readonly string $metadataMimeType = 'application/octet-stream',
        public readonly ?string $metadata = null,
        public readonly ?string $data = null,
    ) {
        parent::__construct(self::SETUP_STREAM_ID);

        if ($this->keepAlive <= 0) {
            throw WrongConfigurationException::wrongKeepAlive();
        }

        if ($this->lifetime <= 0) {
            throw WrongConfigurationException::wrongLifetime();
        }
    }

    public static function fromSettings(ConnectionSettings $settings): self
    {
        return new self(
            $settings->getKeepAlive(),
            $settings->getLifetime(),
            $settings->isReasumeEnable(),
            $settings->isLeaseEnable(),
            $settings->getReasumeToken()
        );
    }

    public function serialize(): string
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($this->streamId);
        $buffer->addUInt16($this->generateTypeAndFlags());
        $buffer->addUInt16(self::MAJOR_VERSION);
        $buffer->addUInt16(self::MINOR_VERSION);
        $buffer->addUInt32($this->keepAlive);
        $buffer->addUInt32($this->lifetime);
        $toReturn = $buffer->toString();

        if ($this->reasumeEnable) {
            $reasumeTokenSize = new ArrayBuffer();
            $reasumeTokenSize->addUInt16(strlen($this->reasumeToken ?? ''));
            $toReturn .= $reasumeTokenSize->toString();
            $toReturn .= $this->reasumeToken ?? '';
        }

        $metaDataMimeTypeLenght = strlen($this->metadataMimeType);
        $toReturn .= chr($metaDataMimeTypeLenght);
        $toReturn .= $this->metadataMimeType;

        $dataMimeTypeLenght = strlen($this->dataMimeType);
        $toReturn .= chr($dataMimeTypeLenght);
        $toReturn .= $this->dataMimeType;

        if ($this->metadata) {
            $metaDataSizeBuffer = new ArrayBuffer();
            $metaDataSizeBuffer->addUInt24(strlen($this->metadata));

            $toReturn .= sprintf('%s%s', $metaDataSizeBuffer->toString(), $this->metadata);
        }

        return sprintf('%s%s', $toReturn, $this->data ?? '');
    }

    public function setData(DataDTO $dataDTO): self
    {
        return new self(
            $this->keepAlive,
            $this->lifetime,
            $this->reasumeEnable,
            $this->leaseEnable,
            $this->reasumeToken,
            $dataDTO->getMimeType(),
            $this->metadataMimeType,
            $this->metadata,
            $dataDTO->getData(),
        );
    }

    public function setMetaData(DataDTO $metaData): self
    {
        return new self(
            $this->keepAlive,
            $this->lifetime,
            $this->reasumeEnable,
            $this->leaseEnable,
            $this->reasumeToken,
            $this->dataMimeType,
            $metaData->getMimeType(),
            $metaData->getData(),
            $this->data
        );
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

    #[Pure]
    public function getMetaData(): DataDTO
    {
        return new DataDTO($this->metadata, $this->metadataMimeType);
    }

    #[Pure]
    public function getData(): DataDTO
    {
        return new DataDTO($this->data, $this->dataMimeType);
    }
}
