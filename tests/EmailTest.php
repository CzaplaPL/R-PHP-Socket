<?php

declare(strict_types=1);

namespace App\Tests;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\IRSocketConnection;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Email\Email;
use Exception;
use InvalidArgumentException;
use phpDocumentor\Reflection\Types\Object_;
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
use Rx\Subject\Subject;
use function Clue\React\Block\await;
use function React\Async\async;

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

//    public function testServer(): void
//    {
////        $this->RSocketServer->close();
//        $connectionBuilder = new ConnectionBuilder();
//        $server = $connectionBuilder->createServer();
//
//
//        $loop = Loop::get();
//
//        $loop->addTimer(30.0, function () use ($loop) {
//            $loop->stop();
//        });
//        $loop->run();
//        $this->assertEquals(true, true);
//
//    }

    public function testConnectionWithDefaultSetupFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();
//
////        $this->expectException(RuntimeException::class);
//
//
//        $data = new DataDTO('data');
//        $metaData = new DataDTO('meta data');
//
//
        $connection = await($client->connect());
        $connection->connect(new ConnectionSettings(keepAlive: 1000,lifetime: 6000));
//        $connection->connection->on("data", function ($data) {
//            var_dump("tu" ,$data);
//        });
        $loop = Loop::get();
//
//        $loop->addTimer(10.0, function () use ($loop) {
//            $loop->stop();
//        });
        $loop->run();
//        $this->assertEquals(true, true);
////
    }

}

