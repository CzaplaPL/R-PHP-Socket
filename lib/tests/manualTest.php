<?php

declare(strict_types=1);

namespace App\Tests;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\IRSocketConnection;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Email\Email;
use App\Frame\FireAndForgetFrame;
use App\Frame\Frame;
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

final class manualTest extends TestCase
{
    const TIMEOUT = 2;

    public function testSendFnFFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await($client->connect());
        $connection->connect(new ConnectionSettings());

        $connection->fireAndForget('witam serdecznie');
    }

    public function testSendRequestResponseFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await($client->connect());
        $connection->connect(new ConnectionSettings());

        $connection->requestResponse('dane');
    }

    public function testSendRequestStreamFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await($client->connect());
        $connection->connect(new ConnectionSettings());

        $connection->requestStream(1,'dane');
    }

    public function testSendRequestChannelFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $client = $connectionBuilder->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await($client->connect());
        $connection->connect(new ConnectionSettings());

        $connection->requestChannel(1,'dane');
    }

    public function testReciveFrame(): void
    {
        $connectionBuilder = new ConnectionBuilder();
        $server = $connectionBuilder->createServer();
        $server->bind();
        $server->newConnections()->subscribe(function (NewConnection $newConnection) {
           $newConnection->connection->onRecivedRequest()->subscribe(function (Frame $frame) {
               var_dump($frame);
           });
        });
        Loop::run();
    }
}

