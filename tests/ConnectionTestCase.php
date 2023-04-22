<?php

namespace App\Tests;

use App\Tests\Stub\CallbackStub;

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