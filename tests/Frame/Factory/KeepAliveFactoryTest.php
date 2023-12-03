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
use App\Frame\KeepAliveFrame;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

final class KeepAliveFactoryTest extends TestCase
{

    public function testCreateErrorFrame(): void
    {
       $keepAliveFrame = new KeepAliveFrame(true, "lorem ipsum");

        $factory = new FrameFactory();

        /**
         * @var KeepAliveFrame $frame
         */
        $frame = $factory->create($keepAliveFrame->serialize());

        $this->assertEquals($keepAliveFrame, $frame);
    }
}
