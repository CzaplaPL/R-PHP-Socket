<?php

namespace App\Tests\Frame;

use App\Connection\Client\ConnectionSettings;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

class SetupFrameTest extends TestCase
{
    public function testCreateDefaultFrame(): void
    {
        $setupFarame = new SetupFrame();
        $setupFarameFromSettings = SetupFrame::fromSettings(New ConnectionSettings());
        $buffer = new ArrayBuffer();
        $buffer->addUInt32(0);
        $buffer->addUInt16(1024);
        $buffer->addUInt16(1);
        $buffer->addUInt16(0);
        $buffer->addUInt32(60000);
        $buffer->addUInt32(300000);
        $expected = $buffer->toString();
        $expected .= chr(24);
        $expected .= "application/octet-stream";
        $expected .= chr(24);
        $expected .= "application/octet-stream";

        $this->assertEquals($expected,$setupFarame->serialize());
        $this->assertEquals($expected,$setupFarameFromSettings->serialize());
    }

    public function testCreateFrameWithReasumeTeokenAndData(): void
    {
        $token = "token";
        $data = new DataDTO("data", "custom");
        $metaData = new DataDTO("meta data", "custom2");
        $setupFarame = new SetupFrame(
            20,
            30,
            true,
            true,
            $token,
        );
        $setupFarame = $setupFarame->setData($data);
        $setupFarame = $setupFarame->setMetaData($metaData);

        $buffer = new ArrayBuffer();
        $buffer->addUInt32(0);
        $buffer->addUInt16(1472);
        $buffer->addUInt16(1);
        $buffer->addUInt16(0);
        $buffer->addUInt32(20);
        $buffer->addUInt32(30);
        $buffer->addUInt16(strlen($token));
        $expected = $buffer->toString();
        $expected .= $token;
        $expected .= chr(strlen($metaData->getMimeType()));
        $expected .= $metaData->getMimeType();
        $expected .= chr(strlen($data->getMimeType()));
        $expected .= $data->getMimeType();

        $metaDataSizeBuffer = new ArrayBuffer();
        $metaDataSizeBuffer->addUInt24(strlen($metaData->getData()));

        $expected .= sprintf('%s%s', $metaDataSizeBuffer->toString(), $metaData->getData());
        $expected .= $data->getData();

        $this->assertEquals($expected,$setupFarame->serialize());
    }

    public function testExpectedExceptionOnWrongKeepAlive(): void
    {
        $this->expectException(ConnectionFailedException::class);
        $setupFarame = new SetupFrame(keepAlive: 0);
    }

    public function testExpectedExceptionOnWrongLifetime(): void
    {
        $this->expectException(ConnectionFailedException::class);
        $setupFarame = new SetupFrame(lifetime: 0);
    }

}
