<?php

namespace App\Tests\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\IRSocketConnection;
use App\Tests\RSocketTestCase;
use React\EventLoop\Loop;
use RuntimeException;
use function Clue\React\Block\await;

final class ClientConnectToServerTest extends RSocketTestCase
{

//    public function testDefaultConnection(): void
//    {
//        $connectionBuilder = new ConnectionBuilder();
//        $client = $connectionBuilder->createClient();
//
//
//        /**
//         * @var IRSocketConnection $connection
//         */
//        $connection = await($client->connect());
//
//        $this->RSocketServer->expectConnectionFromAddress($connection->getLocalAddress() ?? '');
//    }
//
//    public function testConnectionRejectWhenServerNotAvailable(): void
//    {
//        $this->RSocketServer->close();
//        $connectionBuilder = new ConnectionBuilder();
//        $client = $connectionBuilder->createClient();
//
//        $this->expectException(RuntimeException::class);
//
//        /**
//         * @var IRSocketConnection $connection
//         */
//        $connection = await($client->connect());
//    }
}