<?php

namespace App\Tests\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\IRSocketConnection;
use App\Test\RSocketTestCase;
use Exception;
use React\Socket\ConnectionInterface;
use RuntimeException;
use function Clue\React\Block\await;

final class ClientConnectToServerTest extends RSocketTestCase
{

    public function testDefaultConnection() {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());

        $this->RSocketServer->expectConnectionFromAddress($connection->getLocalAddress());
    }

    public function testConnectionRejectWhenServerNotAvailable() {
        $this->RSocketServer->close();
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        $this->expectException(RuntimeException::class);

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());

        $connection->getLocalAddress();
    }
}