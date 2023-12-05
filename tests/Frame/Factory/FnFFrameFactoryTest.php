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
use App\Frame\FireAndForgetFrame;
use App\Frame\SetupFrame;
use PHPUnit\Framework\TestCase;

final class FnFFrameFactoryTest extends TestCase
{

    public function testCreateErrorFrame(): void
    {
       $fnfFrame = new FireAndForgetFrame(200, "mimeType",'metaDataMimeType',null,"data");

        $factory = new FrameFactory();

        $frame = $factory->create($fnfFrame->serialize());

        $this->assertEquals($fnfFrame, $frame);
    }
}
