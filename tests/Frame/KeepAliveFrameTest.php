<?php

namespace App\Tests\Frame;

use App\Connection\Client\ConnectionSettings;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Exception\WrongConfigurationException;
use App\Frame\KeepAliveFrame;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

class KeepAliveFrameTest extends TestCase
{
    public function testCreateDefaultFrame(): void
    {
        $keepAliveFrame = new KeepAliveFrame(true,'data');
        $buffer = new ArrayBuffer();
        $buffer->addUInt32(0);
        $buffer->addUInt16(3200);
        $buffer->addUInt32(0);
        $buffer->addUInt32(0);

        $expected = $buffer->toString();
        $expected .= "data";

        $this->assertEquals($expected,$keepAliveFrame->serialize());
    }
}
