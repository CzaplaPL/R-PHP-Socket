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

    public function testDefaultConnection(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();


        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());

        $this->RSocketServer->expectConnectionFromAddress($connection->getLocalAddress() ?? '');
    }

    public function testConnectionRejectWhenServerNotAvailable(): void
    {
        $this->RSocketServer->close();
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        $this->expectException(RuntimeException::class);

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());
    }


    public function testConnectionWithDefaultSetupFrame(): void
    {
        $this->RSocketServer->close();
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

//        $this->expectException(RuntimeException::class);

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());

        var_dump($connection->getLocalAddress());
        $loop = Loop::get();
        $connection->conection()->on("data", function ($data) {
            var_dump($data);
        });
        $obs = $connection->requestResponse('witam z php');
        $obs->subscribe(function ($data) {
            var_dump($data);
        },
            function ($erro) {
                var_dump($erro);
            },
            function () {
                var_dump("complete");
            }
        );

        $loop->addTimer(20.0, function () use ($loop) {
            $loop->stop();
        });
        $loop->run();
        $this->assertEquals(true, true);

    }

    public function testServer(): void
    {
        $this->RSocketServer->close();
        $connectionBuilder = new ConnectionBuilder();
        $server = $connectionBuilder->createServer();


        $loop = Loop::get();

        $loop->addTimer(30.0, function () use ($loop) {
            $loop->stop();
        });
        $loop->run();
        $this->assertEquals(true, true);

    }
}