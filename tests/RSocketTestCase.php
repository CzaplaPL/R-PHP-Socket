<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Extensions\TestServer;
use PHPUnit\Framework\TestCase;

class RSocketTestCase extends TestCase
{
    protected readonly TestServer $RSocketServer;

    public function __construct()
    {
        parent::__construct();
        $this->RSocketServer = new TestServer();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->RSocketServer->reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->RSocketServer->getConstraints() as $value => $constraint) {
            $this->assertThat($value, $constraint);
        }
        $this->RSocketServer->close();
    }
}
