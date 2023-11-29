<?php

declare(strict_types=1);

namespace App\Tests;

use App\Frame\Factory\FrameFactory;
use App\Frame\Frame;
use App\Frame\SetupFrame;
use App\Tests\Extensions\CallableStub;
use App\Tests\Extensions\TCPTestClient;

use App\Tests\Extensions\TestConnector;
use PHPUnit\Framework\TestCase;

class RSocketTestCase extends TestCase
{
    const TIMEOUT = 1;
    public const TCP_ADDRESS = '127.0.0.1:9091';
    private ?TestConnector $testConnector = null;
    protected TCPTestClient $testClient ;


    public function __construct()
    {
        parent::__construct();
        $this->testClient = new TCPTestClient(new FrameFactory());
    }

    protected function setUp(): void
    {
        parent::setUp();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->getTestConnector()->getConstraints() as $value => $constraint) {
            $this->assertThat($value, $constraint);
        }
        $this->testConnector = null;

        $this->testClient->close();
    }

    protected function getTestConnector(): TestConnector
    {
        if(!$this->testConnector){
            $this->testConnector = new TestConnector();
        }

        return $this->testConnector;
    }

    protected function expectCallableOnce()
    {
        $mock = $this->getMockBuilder(CallableStub::class)->getMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }
}
