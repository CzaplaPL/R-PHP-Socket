<?php

declare(strict_types=1);

namespace App\Tests\Connection\ConnectionConfig;

use App\Connection\Client\Config\TCPConfig;
use App\Tests\ConnectionTestCase;

final class TCPConfigTest extends ConnectionTestCase
{
    public function testDefaultConfigShouldBeEmpty(): void
    {
        $config = new TCPConfig();
        $this->assertEquals('url:80', $config->getUrl());
        $this->assertEquals([], $config->getOptions());
    }
}
