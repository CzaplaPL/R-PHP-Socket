<?php

namespace App\Tests\Connection\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\IRSocketConnection;
use App\Core\Exception\ConnectionFailedException;
use App\Tests\RSocketTestCase;
use RuntimeException;
use function Clue\React\Block\await;

final class ClientConnectToServerTest extends RSocketTestCase
{

    public function testDefaultTCPConnection(): void
    {
        $connectionBuilder = new ConnectionBuilder(self::TCP_ADDRESS);
        $client = $connectionBuilder->createClient();

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());

        $this->TCPRSocketServer->expectConnectionFromAddress($connection->getLocalAddress() ?? '');
    }

    public function testConnectionRejectWhenServerNotAvailable(): void
    {
        $this->TCPRSocketServer->close();
        $connectionBuilder = new ConnectionBuilder(self::TCP_ADDRESS);
        $client = $connectionBuilder->createClient();

        $this->expectException(ConnectionFailedException::class);

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());
    }
}