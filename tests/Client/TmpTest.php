<?php

namespace App\Tests\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\IRSocketConnection;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use RuntimeException;
use function Clue\React\Block\await;

final class TmpTest extends TestCase
{

//    public function testDefaultConnection(): void {
//        $connectionBuilder = new ConnectionBuilder();
//        $client = $connectionBuilder->createClient();
//
//        /**
//         * @var IRSocketConnection $connection
//         */
//        $connection = await($client->connect());
//
//        $this->RSocketServer->expectConnectionFromAddress($connection->getLocalAddress()??'');
//    }
//
//    public function testConnectionRejectWhenServerNotAvailable(): void {
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


    public function testConnectionWithDefaultSetupFrame(): void {
//        $this->RSocketServer->close();
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

//        $this->expectException(RuntimeException::class);

        /**
         * @var IRSocketConnection $connection
         */
        $connection = await($client->connect());
        $loop = Loop::get();


        $loop->addTimer(5.0, function () use ($loop) {
            $loop->stop();
        });
        $loop->run();
        $this->assertEquals(true,true);
    }

    public function testTMPFrame(): void
    {
        $data = 20;

        $pack = str_split(pack("N",$data));

//        var_dump(ord($data[0]));
        var_dump(ord($pack[0]));
        var_dump(ord($pack[1]));
        var_dump(ord($pack[2]));
        var_dump(ord($pack[3]));
        var_dump(unpack("N",pack("N",$data)));
//        var_dump(chr(unpack("C",pack("C",ord($data[0])))[1]));
    }
}