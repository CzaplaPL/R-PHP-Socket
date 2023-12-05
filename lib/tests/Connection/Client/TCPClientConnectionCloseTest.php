<?php

namespace App\Tests\Connection\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\Client\TCPClient;
use App\Connection\ClosedConnection;
use App\Connection\RSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\SetupFrame;
use App\Tests\Extensions\TestConnector;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class TCPClientConnectionCloseTest extends RSocketTestCase
{
    public function testEmitCloseWithCompleteOnServerDisconection(): void
    {
        $server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $server->bind();

        $client  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(), self::TIMEOUT));

        $closePromise = new Promise(function (callable $resolver) use ($connection) {
            $connection->onClose()->subscribe(
                onNextOrObserver: function (ClosedConnection $closedConnection) {
                    $this->assertNull($closedConnection->errorException);
                }, onCompleted: function () use ($resolver) {
                $resolver(true);
            }
            );
        });

        $server->close();

//        await($closePromise);
        await(timeout($closePromise, self::TIMEOUT));
    }
}
