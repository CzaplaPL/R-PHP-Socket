<?php

declare(strict_types=1);

namespace App\Tests\Connection;

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
//
//final class ConnectionBuilderTest extends ConnectionTestCase
//{
//    private ?ConnectionInterface $globalConnection;
//
//    private $peer;
//    public function testCreateSimpleTcpClient(): void
//    {
////        $server = new TcpServer(0);
////
////       // $ConnectionBuilder = new ConnectionBuilder();
////
////
////        $server->on('connection', $this->expectedCallback());
////
////        $peer = new Promise(function ($resolve, $reject) use ($server) {
////            $server->on('connection', $resolve);
////        });
////
////        //$connection = $connectionBuilder->createConnection();
////
////        Block\await($peer, null, self::TIMEOUT);
////
////        $server->close();
////        $connection->close();
//        $server = new TcpServer(0);
//
//        $connector = new Connector();
//
//
//        $server->on('connection', function (ConnectionInterface $conn) use ($server) {
//            $this->peer = new Promise(function ($resolve, $reject) use ($conn) {
//                $conn->on('data', function ($data) {
//                    var_dump($data);
//                });
//
//            });
//        });
//
//        $promise = $connector->connect($server->getAddress());
//
//        $promise->then(function (ConnectionInterface $connection) {
//             $this->globalConnection = $connection;
//            var_dump("eee222");
//
//            $connection->write("test");
//        });
//        Block\await($promise, null, self::TIMEOUT);
//        $this->globalConnection->write("test2222");
//        Block\await($this->peer, null, self::TIMEOUT);
//
//        $server->close();
//
//
//
//    }
//
//    public function testCreateSimpleTcpClient2(): void
//    {
//        $server = new TcpServer(0);
//
//        $connector = new Connector();
//
//        $data = str_repeat('c', 100000);
//        $server->on('connection', function (ConnectionInterface $peer) use ($data) {
//            $peer->write($data);
//        });
//
//        $connecting = $connector->connect($server->getAddress());
//
//        $promise = new Promise(function ($resolve, $reject) use ($connecting) {
//            $connecting->then(function (ConnectionInterface $connection) use ($resolve) {
//                $received = 0;
//                $connection->on('data', function ($chunk) use (&$received, $resolve) {
//                    $received += strlen($chunk);
//
//                    if ($received >= 100000) {
//                        $resolve($received);
//                    }
//                });
//            }, $reject);
//        });
//
//        $received = Block\await($promise, null, self::TIMEOUT);
//
//        $this->assertEquals(strlen($data), $received);
//
//        $connecting->then(function (ConnectionInterface $connection) {
//            $connection->close();
//        });
//
//    }
//}
