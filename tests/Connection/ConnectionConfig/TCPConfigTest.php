<?php

declare(strict_types=1);

namespace App\Tests\Connection\ConnectionConfig;

use App\Connection\Config\TCPConfig;
use App\Email\Email;
use App\Tests\ConnectionTestCase;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;
use Clue\React\Block;
use React\Socket\Connector;
use React\Socket\SocketServer;
use React\Stream\WritableResourceStream;
use React\Promise\Promise;
use React\EventLoop\Loop;
use React\Socket\TcpConnector;
use React\Socket\SecureConnector;
use React\Socket\TcpServer;

final class TCPConfigTest extends ConnectionTestCase
{
    public function testDefaultConfigShouldBeEmpty(): void
    {
        $config = new TCPConfig();
        $this->assertEquals('', $config->getUrl());
        $this->assertEquals([], $config->getOptions());
    }
}
