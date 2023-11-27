<?php

namespace App\Tests\Connection\Server;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\RSocketConnection;
use App\Connection\Server\TCPServer;
use App\Core\Exception\ConnectionFailedException;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class TCPServerTest extends RSocketTestCase
{

    public function testSuccesfullConectWithDefaultSetupFrame(): void
    {
        $this->TCPRSocketServer->close();
        $server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $server->bind();

        $result = await($this->testClient->connect(self::TCP_ADDRESS));
        $this->testClient->SendSetupFrame();

        await(new Promise(static function(callable $resolver) use ($server){
            $server->newConnections()->subscribe(static function() use($resolver) {
                $resolver(true);
            });
        }));
        $this->assertCount(1,$server->getConnections());

        $server->close();
    }

    public function testNotConectWithOutSetupFrame(): void
    {
        $this->TCPRSocketServer->close();
        $server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $server->bind();

        $result = await($this->testClient->connect(self::TCP_ADDRESS));

        $this->assertCount(0,$server->getConnections());
        $this->testClient->close();
        $server->close();


        Loop::stop();
    }
}
