<?php

namespace App\Tests;

use App\Tests\Stub\CallbackStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionTestCase extends TestCase
{
    protected const TIMEOUT = 2;

    final protected function expectedCallback(): callable
    {
        $mock = $this->getMockBuilder(CallbackStub::class)->getMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }
}