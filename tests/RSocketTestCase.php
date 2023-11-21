<?php

declare(strict_types=1);

namespace App\Tests;

use App\Frame\Frame;
use App\Frame\SetupFrame;
use App\Tests\Extensions\TCPTestServer;
use App\Tests\Extensions\TestConnector;
use PHPUnit\Framework\TestCase;

class RSocketTestCase extends TestCase
{
    public const TCP_ADDRESS = '127.0.0.1:9091';
    protected readonly TCPTestServer $TCPRSocketServer;
    private ?TestConnector $testConnector = null;


    public function __construct()
    {
        parent::__construct();
        $this->TCPRSocketServer = new TCPTestServer(self::TCP_ADDRESS);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->TCPRSocketServer->reset();
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->TCPRSocketServer->getConstraints() as $value => $constraint) {
            $this->assertThat($value, $constraint);
        }
        $this->TCPRSocketServer->close();

        foreach ($this->getTestConnector()->getConstraints() as $value => $constraint) {
            $this->assertThat($value, $constraint);
        }
        $this->testConnector = null;
    }

    protected function getTestConnector(): TestConnector
    {
        if(!$this->testConnector){
            $this->testConnector = new TestConnector();
        }

        return $this->testConnector;
    }
}
