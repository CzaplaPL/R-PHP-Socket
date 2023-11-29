<?php

namespace App\Tests\Frame\Factory;

use App\Core\ArrayBuffer;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\CreateFrameException;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Core\Exception\WrongUrlException;
use App\Core\Url;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\Factory\FrameFactory;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

final class ErrorFrameFactoryTest extends TestCase
{

    public function testCreateErrorFrame(): void
    {
       $errorFrame = new ErrorFrame(200, ErrorType::CONNECTION_ERROR, "lorem ipsum");

        $factory = new FrameFactory();

        $frame = $factory->create($errorFrame->serialize());

        $this->assertEquals($errorFrame, $frame);
    }
}
