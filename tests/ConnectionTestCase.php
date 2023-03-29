<?php

namespace App\Tests;

use App\Test\RSocketTestCase;
use App\Tests\Stub\CallbackStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConnectionTestCase extends RSocketTestCase
{
    protected const TIMEOUT = 4;

    final protected function expectedCallback(): callable
    {
        $mock = $this->getMockBuilder(CallbackStub::class)->getMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }
}