<?php

namespace App\Tests\Frame\Factory;

use App\Core\ArrayBuffer;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\CreateFrameException;
use App\Core\Exception\WrongUrlException;
use App\Core\Url;
use App\Frame\Factory\FrameFactory;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

final class SetupFrameFactoryTest extends TestCase
{
    /**
     * @dataProvider setupFarameProvider
     * @param array{
     *     keepAlive: int,
     *     lifetime: int,
     *     reasumeEnable: bool,
     *     leaseEnable: bool,
     *     reasumeToken: ?string,
     *     dataMimeType: string,
     *     metadataMimeType: string,
     *     metadata: string,
     *     data: string
     * } $frameData
     */
    public function testCreateSetupFrame(array $frameData): void
    {
        $sendedFrame = new SetupFrame(
            $frameData['keepAlive'],
            $frameData['lifetime'],
            $frameData['reasumeEnable'],
            $frameData['leaseEnable'],
            $frameData['reasumeToken'],
            $frameData['dataMimeType'],
            $frameData['metadataMimeType'],
            $frameData['metadata'],
            $frameData['data'],
        );

        $factory = new FrameFactory();

        $frame = $factory->create($sendedFrame->serialize());

        $this->assertEquals($sendedFrame, $frame);
    }

    public function testThrowExceptionOnWrongStreamIdInSetupFrame(): void
    {
        $sendedFrame = (new SetupFrame())->serialize();
        $stringArray = str_split($sendedFrame);
        $buffer = new ArrayBuffer(array_map(static fn ($char) => ord($char), $stringArray));
        $oldBuffer = $buffer->getBuffer();
        $oldBuffer[3] = 1;
        $newBuffer = new ArrayBuffer($oldBuffer);

        $this->expectException(CreateFrameException::class);

        $factory = new FrameFactory();
        $factory->create($newBuffer->toString());
    }

    public function testThrowExceptionOnMajorVersionStreamIdInSetupFrame(): void
    {
        $sendedFrame = (new SetupFrame())->serialize();
        $stringArray = str_split($sendedFrame);
        $buffer = new ArrayBuffer(array_map(static fn ($char) => ord($char), $stringArray));
        $oldBuffer = $buffer->getBuffer();
        $oldBuffer[6] = 1;
        $newBuffer = new ArrayBuffer($oldBuffer);

        $this->expectException(CreateFrameException::class);

        $factory = new FrameFactory();
        $factory->create($newBuffer->toString());
    }

    public function testThrowExceptionOnMinorVersionStreamIdInSetupFrame(): void
    {
        $sendedFrame = (new SetupFrame())->serialize();
        $stringArray = str_split($sendedFrame);
        $buffer = new ArrayBuffer(array_map(static fn ($char) => ord($char), $stringArray));
        $oldBuffer = $buffer->getBuffer();
        $oldBuffer[8] = 1;
        $newBuffer = new ArrayBuffer($oldBuffer);

        $this->expectException(CreateFrameException::class);

        $factory = new FrameFactory();
        $factory->create($newBuffer->toString());
    }

    public function setupFarameProvider(): mixed
    {
        return [
            'default' => [[
                'keepAlive' => 60000,
                'lifetime' => 300000,
                'reasumeEnable' => false,
                'leaseEnable' => false,
                'reasumeToken' => null,
                'dataMimeType' => 'application/octet-stream',
                'metadataMimeType' => 'application/octet-stream',
                'metadata' => null,
                'data' => null,
            ]],
            'withReasumeToken' => [[
                'keepAlive' => 60000,
                'lifetime' => 300000,
                'reasumeEnable' => true,
                'leaseEnable' => true,
                'reasumeToken' => 'token',
                'dataMimeType' => 'application/octet-stream',
                'metadataMimeType' => 'application/octet-stream',
                'metadata' => null,
                'data' => null,
            ]],
            'changeMimeType' => [[
                'keepAlive' => 60000,
                'lifetime' => 300000,
                'reasumeEnable' => false,
                'leaseEnable' => false,
                'reasumeToken' => null,
                'dataMimeType' => 'mime1',
                'metadataMimeType' => 'mime2',
                'metadata' => null,
                'data' => null,
            ]],
            'withMetaData' => [[
                'keepAlive' => 60000,
                'lifetime' => 300000,
                'reasumeEnable' => false,
                'leaseEnable' => false,
                'reasumeToken' => null,
                'dataMimeType' => 'mime1',
                'metadataMimeType' => 'mime2',
                'metadata' => ' Meta Data ',
                'data' => null,
            ]],
            'withMetaDataAndData' => [[
                'keepAlive' => 60000,
                'lifetime' => 300000,
                'reasumeEnable' => false,
                'leaseEnable' => false,
                'reasumeToken' => null,
                'dataMimeType' => 'mime1',
                'metadataMimeType' => 'mime2',
                'metadata' => ' Meta Data ',
                'data' => 'data',
            ]],
        ];
    }
}
