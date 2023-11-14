<?php

namespace App\Tests\Frame;

use App\Connection\Client\ConnectionSettings;
use App\Core\ArrayBuffer;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

class SetupFrameTest extends TestCase
{
    public function testCreateDefaultFrame()
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

}
