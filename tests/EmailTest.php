<?php

declare(strict_types=1);

namespace App\Tests;

use App\Email\Email;
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

final class EmailTest extends TestCase
{
    const TIMEOUT = 2;

    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            Email::class,
            Email::fromString('user@example.com')
        );
    }

    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Email::fromString('user@example.com')
        );
    }

//    public function testSocket(): void
//    {
//        $this->server = new TcpServer(0);
//
//        $this->connector = new Connector();
//
//        $data = str_repeat('c', 100000);
//        $this->server->on('connection', function (ConnectionInterface $peer) use ($data) {
//            $peer->write($data);
//        });
//
//        $connecting = $this->connector->connect($this->server->getAddress());
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



//        $socket = new SocketServer('127.0.0.1:8080');
//
//        $socket->on('connection', function (ConnectionInterface $connection) {
//            $connection->write("Hello " . $connection->getRemoteAddress() . "!\n");
//            $connection->write("Welcome to this amazing server!\n");
//            $connection->write("Here's a tip: don't say anything.\n");
//
//            $connection->on('data', function ($data) use ($connection) {
//                $connection->close();
//            });
//        });
//
//        $connector = new Connector();
//
//        $connector->connect('127.0.0.1:8080')->then(function (ConnectionInterface $connection) {
//            $connection->pipe(new WritableResourceStream(STDOUT));
//            $connection->write("Hello World!\n");
//        }, function (Exception $e) {
//            echo 'Error: ' . $e->getMessage() . PHP_EOL;
//        });


//        // immediately close connection and server once connection is in
//                // immediately close connection and server once connection is in
//        $server = new TcpServer(0);
//        $server->on('connection', function (ConnectionInterface $conn) use ($server) {
//            $conn->close();
//            $server->close();
//        });
//
//
//        $connector = new TcpConnector();
//        $connector->connect($server->getAddress())->then(function (ConnectionInterface $conn)  {
//            $conn->write('hello');
//        });
//
//        Loop::run();
//    }

//    public function testSocket(): void
//    {
//        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
//        $connector = new TcpConnector($loop);
//
//        $server = new TcpServer(0, $loop);
//
//        $valid = false;
//        $loop->expects($this->once())->method('addWriteStream')->with($this->callback(function ($arg) use (&$valid) {
//            $valid = is_resource($arg);
//            return true;
//        }));
//        $connector->connect($server->getAddress());
//
//        $this->assertTrue($valid);
//    }

//    public function connectionToTcpServerWillCloseWhenOtherSideCloses()
//    {
//        // immediately close connection and server once connection is in
//        $server = new TcpServer(0);
//        $server->on('connection', function (ConnectionInterface $conn) use ($server) {
//            $conn->close();
//            $server->close();
//        });
//
//        $once = $this->expectCallableOnce();
//        $connector = new TcpConnector();
//        $connector->connect($server->getAddress())->then(function (ConnectionInterface $conn) use ($once) {
//            $conn->write('hello');
//            $conn->on('close', $once);
//        });
//
//        Loop::run();
//    }

}
