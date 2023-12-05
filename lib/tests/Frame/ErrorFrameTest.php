<?php

namespace App\Tests\Frame;

use App\Connection\Client\ConnectionSettings;
use App\Core\ArrayBuffer;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

class ErrorFrameTest extends TestCase
{
    public function testCreateErrorFrame(): void
    {
        $streamId =200;
        $type = ErrorType::CONNECTION_ERROR;
        $message = 'blad';
        $errorFarame = new ErrorFrame(
            $streamId,
            $type,
            $message
        );
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($streamId);
        $buffer->addUInt16(11264);
        $buffer->addUInt32(ErrorType::CONNECTION_ERROR->value);
        $expected = $buffer->toString();
        $expected .=$message;


        $this->assertEquals($expected,$errorFarame->serialize());
    }
}
