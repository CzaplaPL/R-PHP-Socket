<?php

namespace App\Tests\Connection\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\RSocketConnection;
use App\Connection\TCPRSocketConnection;
use App\Core\Exception\ConnectionFailedException;
use App\Tests\RSocketTestCase;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use RuntimeException;
use function Clue\React\Block\await;

final class ClientConnectToServerTest extends RSocketTestCase
{

    public function testDefaultTCPConnection(): void
    {
        $connectionBuilder = new ConnectionBuilder(self::TCP_ADDRESS);
        $client = $connectionBuilder->createClient();

        /**
         * @var TCPRSocketConnection $connection
         */
        $connection = await($client->connect());

        $this->TCPRSocketServer->expectConnectionFromAddress( $connection->getLocalAddress());
    }

    public function testConnectionRejectWhenServerNotAvailable(): void
    {
        $this->TCPRSocketServer->close();
        $connectionBuilder = new ConnectionBuilder(self::TCP_ADDRESS);
        $client = $connectionBuilder->createClient();

        $this->expectException(ConnectionFailedException::class);

        /**
         * @var RSocketConnection $connection
         */
        $connection = await($client->connect());
    }
}