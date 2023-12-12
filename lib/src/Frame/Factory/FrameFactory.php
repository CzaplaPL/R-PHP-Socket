<?php

declare(strict_types=1);

namespace App\Frame\Factory;

use App\Core\ArrayBuffer;
use App\Core\Exception\CreateFrameException;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Frame\CancelFrame;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\FireAndForgetFrame;
use App\Frame\Frame;
use App\Frame\KeepAliveFrame;
use App\Frame\LeaseFrame;
use App\Frame\PayloadFrame;
use App\Frame\ReasumeFrame;
use App\Frame\RequestChannelFrame;
use App\Frame\RequestNFrame;
use App\Frame\RequestResponseFrame;
use App\Frame\RequestStreamFrame;
use App\Frame\SetupFrame;

class FrameFactory implements IFrameFactory
{
    public function create(string $data): Frame
    {
        $stringArray = str_split($data);
        $buffer = new ArrayBuffer(array_map(static fn($char) => ord($char), $stringArray));
        $offset = 0;
        $streamId = $buffer->getUInt32($offset);
        $offset += 4;
        $typeAndFlag = $buffer->getUInt16($offset);
        $type = $typeAndFlag >> 10;

        return match ($type) {
            1 => $this->createSetupType($buffer, $offset, $streamId, $data),
            2 => $this->createLeaseType($buffer, $offset, $streamId, $data),
            3 => $this->createKeepAliveType($buffer, $offset, $streamId, $data),
            4 => $this->createRequestResponseType($buffer, $offset, $streamId, $data),
            5 => $this->createFnFType($buffer, $offset, $streamId, $data),
            6 => $this->createRequestStreamType($buffer, $offset, $streamId, $data),
            7 => $this->createRequestChannelType($buffer, $offset, $streamId, $data),
            8 => $this->createRequestNType($buffer, $offset, $streamId, $data),
            9 => $this->createCancelType($buffer, $offset, $streamId, $data),
            10 => $this->createPayloadType($buffer, $offset, $streamId, $data),
            11 => $this->createErrorType($buffer, $offset, $streamId, $data),
            13 => $this->createReasumeType($buffer, $offset, $streamId, $data),
            default => throw CreateFrameException::unknowType($type)
        };
    }

    private function createPayloadType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): PayloadFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;
        $follows = ($typeAndFlag & 0x100) === 128;
        $complete = ($typeAndFlag & 0x40) === 64;
        $next = ($typeAndFlag & 0x20) === 32;

        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new PayloadFrame($streamId, $data, $follows, $complete, $next, $metaData);
    }

    private function createSetupType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): SetupFrame
    {
        if (0 !== $streamId) {
            throw CreateFrameException::wrongStreamIdToSetupFrame($streamId);
        }

        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;
        $reasume = ($typeAndFlag & 0x80) === 128;
        $lease = ($typeAndFlag & 0x40) === 64;

        $majorVersion = $buffer->getUInt16($offset);
        $offset += 2;

        $minorVersion = $buffer->getUInt16($offset);
        $offset += 2;

        if (Frame::MAJOR_VERSION !== $majorVersion || Frame::MINOR_VERSION !== $minorVersion) {
            throw CreateFrameOnUnsuportedVersionException::versionNotSuported($majorVersion, $minorVersion);
        }

        $keepAlive = $buffer->getUInt32($offset);
        $offset += 4;

        $lifetime = $buffer->getUInt32($offset);
        $offset += 4;
        $reasumeTokem = null;
        if ($reasume) {
            $reasumeTokenLenght = $buffer->getUInt16($offset);
            $offset += 2;

            $reasumeTokem = substr($data, $offset, $reasumeTokenLenght);
            $offset += $reasumeTokenLenght;
        }

        $metaDataMimeTypeLenght = $buffer->getUInt8($offset);
        ++$offset;
        $metaDataMimeType = substr($data, $offset, $metaDataMimeTypeLenght);
        $offset += $metaDataMimeTypeLenght;

        $dataMimeTypeLenght = $buffer->getUInt8($offset);
        ++$offset;
        $dataMimeType = substr($data, $offset, $dataMimeTypeLenght);
        $offset += $dataMimeTypeLenght;

        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new SetupFrame(
            keepAlive: $keepAlive,
            lifetime: $lifetime,
            reasumeEnable: $reasume,
            leaseEnable: $lease,
            reasumeToken: $reasumeTokem,
            dataMimeType: $dataMimeType,
            metadataMimeType: $metaDataMimeType,
            metadata: $metaData,
            data: $data
        );
    }

    private function createErrorType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): ErrorFrame
    {
        $offset += 2;
        $errorType = $buffer->getUInt32($offset);
        $offset += 4;
        $message = substr($data, $offset);

        return new ErrorFrame(
            $streamId,
            ErrorType::tryFrom($errorType),
            $message
        );
    }

    private function createKeepAliveType(ArrayBuffer $buffer, int $offset, int $streamId, string $data)
    {
        if (0 !== $streamId) {
            throw CreateFrameException::wrongStreamIdToKeepAliveFrame($streamId);
        }

        $typeAndFlag = $buffer->getUInt16($offset);

        $needResponse = ($typeAndFlag & 0x80) === 128;

        $tmp = substr($data, $offset + 10);

        return new KeepAliveFrame(
            $needResponse,
            substr($data, $offset + 10)
        );
    }

    private function createFnFType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): FireAndForgetFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;

        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new FireAndForgetFrame(
            $streamId,
            $data,
            $metaData,
        );
    }

    private function createRequestResponseType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): RequestResponseFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;

        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new RequestResponseFrame(
            $streamId,
            $data,
            $metaData,
        );
    }

    private function createRequestStreamType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): RequestStreamFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;
        $requestN = $buffer->getUInt32($offset);
        $offset += 4;
        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new RequestStreamFrame(
            $streamId,
            $requestN,
            $data,
            $metaData,
        );
    }

    private function createRequestNType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): RequestNFrame
    {
        $offset += 2;
        $requestN = $buffer->getUInt32($offset);

        return new RequestNFrame(
            $streamId,
            $requestN,
        );
    }

    private function createRequestChannelType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): RequestChannelFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;
        $requestN = $buffer->getUInt32($offset);
        $offset += 4;
        $metaData = null;
        if ($hasMetaData) {
            $metaDataSize = $buffer->getUInt24($offset);
            $offset += 3;
            $metaData = substr($data, $offset, $metaDataSize);
            $offset += $metaDataSize;
        }
        $data = substr($data, $offset);

        return new RequestChannelFrame(
            $streamId,
            $requestN,
            $data,
            $metaData,
        );
    }

    private function createCancelType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): CancelFrame
    {

        return new CancelFrame(
            $streamId,
        );
    }

    private function createReasumeType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): ReasumeFrame
    {
        if (0 !== $streamId) {
            throw CreateFrameException::wrongStreamIdToSetupFrame($streamId);
        }

        $offset += 2;


        $majorVersion = $buffer->getUInt16($offset);
        $offset += 2;

        $minorVersion = $buffer->getUInt16($offset);
        $offset += 2;

        if (Frame::MAJOR_VERSION !== $majorVersion || Frame::MINOR_VERSION !== $minorVersion) {
            throw CreateFrameOnUnsuportedVersionException::versionNotSuported($majorVersion, $minorVersion);
        }


        $reasumeTokenLenght = $buffer->getUInt16($offset);
        $offset += 2;

        $reasumeTokem = substr($data, $offset, $reasumeTokenLenght);
        $offset += $reasumeTokenLenght + 4;

        $receivedPosition = $buffer->getUInt32($offset);
        $offset +=  8;

        $availablePosidtion = $buffer->getUInt32($offset);
        return new ReasumeFrame(
            $reasumeTokem,
            $receivedPosition,
            $availablePosidtion
        );
    }

    private function createLeaseType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): LeaseFrame
    {
        if (0 !== $streamId) {
            throw CreateFrameException::wrongStreamIdToSetupFrame($streamId);
        }

        $offset += 2;
        $ttl = $buffer->getUInt32($offset);
        $offset += 4;
        $limit = $buffer->getUInt32($offset);

        return new LeaseFrame(
            $ttl,
            $limit
        );
    }
}
