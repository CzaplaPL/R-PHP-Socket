<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Extensions\TCPTestServer;
use PHPUnit\Framework\TestCase;

class RSocketTestCase extends TestCase
{
    public const TCP_ADDRESS = '127.0.0.1:9091';
    protected readonly TCPTestServer $TCPRSocketServer;

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
    }
}
