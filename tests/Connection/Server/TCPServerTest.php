<?php

namespace App\Tests\Connection\Server;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\ClosedConnection;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Connection\Server\IRSocketServer;
use App\Connection\Server\ServerSettings;
use App\Connection\Server\TCPServer;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Exception\ServerErrorException;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\Frame;
use App\Frame\SetupFrame;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class TCPServerTest extends RSocketTestCase
{
    private ?IRSocketServer $server = null;

    public function testErrorOnDubleBindServer(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();
        $this->expectException(ServerErrorException::class);
        $this->server->bind();
    }

    public function testCanBindServerAfterClose(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();
        $this->server->close();
        $this->server->bind();
        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));
        $this->assertCount(1, $this->server->getConnections());
    }

    public function testSuccesfullConectWithDefaultSetupFrame(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));


        $this->testClient->SendSetupFrame();

        await(timeout(new Promise( function(callable $resolver){
            $this->server->newConnections()->take(1)->subscribe(static function() use($resolver) {
                $resolver(true);
            });
        }),self::TIMEOUT));

        $this->assertCount(1, $this->server->getConnections());
        $this->assertTrue(current( $this->server->getConnections())->isConnectSetuped());
    }

    public function testSuccesfullConectWithReasumeAndDataInSetupFrame(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind(new ServerSettings(reasumeEnable: true));

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $data =  new DataDTO("data");
        $metaData =  new DataDTO("meta-data");
        $this->testClient->SendSetupFrame(new ConnectionSettings(reasumeEnable: true),$data,$metaData);

        await(timeout(new Promise(function(callable $resolver) use ( $data, $metaData){
            $this->server->newConnections()->take(1)->subscribe(function(NewConnection $newConnection) use($resolver, $data, $metaData) {
                $this->assertEquals($newConnection->frame->getMetaData(), $metaData);
                $this->assertEquals($newConnection->frame->getData(), $data);
                $resolver(true);
            });
        }),self::TIMEOUT));
        $this->assertCount(1, $this->server->getConnections());
        $this->assertTrue( current($this->server->getConnections())->isConnectSetuped());
    }

    public function testConectToServerThatDoNotSuportReasume(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedErrorFrame = new Promise( function(callable $resolver){
            $this->testClient->recivedMessage()->take(1)->subscribe(function(Frame $frame) use($resolver) {
                $this->assertInstanceOf(ErrorFrame::class, $frame);

                /**
                 * @var ErrorFrame $errorFrame
                 */
                $errorFrame = $frame instanceof ErrorFrame ? $frame: throw new \TypeError("Expected ErrorFrame");

                $this->assertEquals(0, $errorFrame->streamId());
                $this->assertEquals(ErrorType::UNSUPPORTED_SETUP, $errorFrame->type());
                $this->assertEquals("No resume support", $errorFrame->errorMesage());
                $resolver(true);
            });
        });

        $expectedCloseConnection = new Promise( function(callable $resolver){
            $this->server->closedConnections()->take(1)->subscribe(function() use($resolver) {
                $resolver(true);
            });
        });

        $this->testClient->SendSetupFrame(new ConnectionSettings(reasumeEnable: true));

        await(timeout($expectedErrorFrame,self::TIMEOUT));
        await(timeout($expectedCloseConnection,self::TIMEOUT));
        $this->assertCount(0, $this->server->getConnections());
    }

    public function testSuccesfullConectWithLeaseInSetupFrame(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind(new ServerSettings(leaseEnable: true));

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));


        $this->testClient->SendSetupFrame(new ConnectionSettings(leaseEnable: true));

        await(timeout(new Promise( function(callable $resolver){
            $this->server->newConnections()->take(1)->subscribe(function(NewConnection $newConnection) use($resolver) {
                $this->assertTrue($newConnection->frame->leaseEnable);

                $resolver(true);
            });
        }),self::TIMEOUT));
        $this->assertCount(1, $this->server->getConnections());
        $this->assertTrue( current($this->server->getConnections())->isConnectSetuped());
    }

    public function testConectToServerThatDoNotSuportLease(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedErrorFrame = new Promise( function(callable $resolver){
            $this->testClient->recivedMessage()->take(1)->subscribe(function(Frame $frame) use($resolver) {
                $this->assertInstanceOf(ErrorFrame::class, $frame);

                /**
                 * @var ErrorFrame $errorFrame
                 */
                $errorFrame = $frame instanceof ErrorFrame ? $frame: throw new \TypeError("Expected ErrorFrame");



                $this->assertEquals(0, $errorFrame->streamId());
                $this->assertEquals(ErrorType::UNSUPPORTED_SETUP, $errorFrame->type());
                $this->assertEquals("No lease support", $errorFrame->errorMesage());
                $resolver(true);
            });
        });

        $this->testClient->SendSetupFrame(new ConnectionSettings(leaseEnable: true));

        await(timeout($expectedErrorFrame,self::TIMEOUT));
        $this->assertCount(0, $this->server->getConnections());
    }

    public function testConectToServerWithoutLeaseWhenSerwerRequireLease(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind(new ServerSettings(leaseRequire: true));

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedErrorFrame = new Promise( function(callable $resolver){
            $this->testClient->recivedMessage()->take(1)->subscribe(function(Frame $frame) use($resolver) {
                $this->assertInstanceOf(ErrorFrame::class, $frame);

                /**
                 * @var ErrorFrame $errorFrame
                 */
                $errorFrame = $frame instanceof ErrorFrame ? $frame: throw new \TypeError("Expected ErrorFrame");



                $this->assertEquals(0, $errorFrame->streamId());
                $this->assertEquals(ErrorType::REJECTED_SETUP, $errorFrame->type());
                $this->assertEquals("Server need lease", $errorFrame->errorMesage());
                $resolver(true);
            });
        });

        $this->testClient->SendSetupFrame(new ConnectionSettings());

        await(timeout($expectedErrorFrame,self::TIMEOUT));
        $this->assertCount(0, $this->server->getConnections());
    }

    public function testClientCloseConnectionWithoutReasume(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedCloseConnection = new Promise( function(callable $resolver){
            $this->server->closedConnections()->take(1)->subscribe(
                function (ClosedConnection $closedConnection) use($resolver) {
                    $this->assertNull($closedConnection->errorException);
                    $resolver(true);
                }
            );
        });

        $this->testClient->SendSetupFrame();

        $this->testClient->close();
        await(timeout($expectedCloseConnection,self::TIMEOUT));
        $this->assertCount(0, $this->server->getConnections());
    }

    public function testClientCloseConnectionWithReasume(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind(new ServerSettings(reasumeEnable: true));

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedCloseConnection = new Promise( function(callable $resolver){
            $this->server->closedConnections()->take(1)->subscribe(
                function (ClosedConnection $closedConnection) use($resolver) {
                    $this->assertNull($closedConnection->errorException);
                    $resolver(true);
                }
            );
        });

        $this->testClient->SendSetupFrame(new ConnectionSettings(reasumeEnable: true));
        await(timeout(new Promise(function(callable $resolver){
            $this->server->newConnections()->take(1)->subscribe(function(NewConnection $newConnection) use($resolver) {
                $resolver(true);
            });
        }),self::TIMEOUT));
        $this->testClient->close();
        await(timeout($expectedCloseConnection,self::TIMEOUT));
        $this->assertCount(1, $this->server->getConnections());
    }

    public function testErrorOnDubleSendSetupFrame(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));
        $this->testClient->SendSetupFrame();

        await(timeout(new Promise( function(callable $resolver) {
            $this->server->newConnections()->take(1)->subscribe(static function() use($resolver) {
                $resolver(true);
            });
        }),self::TIMEOUT));

        $this->assertCount(1, $this->server->getConnections());
        $this->assertTrue( current( $this->server->getConnections())->isConnectSetuped());

        $expectedErrorFrame = new Promise( function(callable $resolver){
            $this->testClient->recivedMessage()->take(1)->subscribe(function(Frame $frame) use($resolver) {
                $this->assertInstanceOf(ErrorFrame::class, $frame);

                /**
                 * @var ErrorFrame $errorFrame
                 */
                $errorFrame = $frame instanceof ErrorFrame ? $frame: throw new \TypeError("Expected ErrorFrame");
                $this->assertEquals(0, $errorFrame->streamId());
                $this->assertEquals(ErrorType::REJECTED_SETUP, $errorFrame->type());
                $this->assertEquals("The connection is already setuped", $errorFrame->errorMesage());
                $resolver(true);
            });
        });

        $this->testClient->SendSetupFrame();
        await(timeout($expectedErrorFrame,self::TIMEOUT));
        $this->assertCount(1, $this->server->getConnections());
        $this->assertTrue( current( $this->server->getConnections())->isConnectSetuped());
    }

    public function testErrorOnWrongVersionInSetupFrame(): void
    {
        $this->server  = (new ConnectionBuilder(self::TCP_ADDRESS))
            ->createServer();

        $this->server->bind();

        await(timeout($this->testClient->connect(self::TCP_ADDRESS),self::TIMEOUT));

        $expectedErrorFrame = new Promise( function(callable $resolver) {
            $this->testClient->recivedMessage()->take(1)->subscribe(function(Frame $frame) use($resolver) {
                $this->assertInstanceOf(ErrorFrame::class, $frame);

                /**
                 * @var ErrorFrame $errorFrame
                 */
                $errorFrame = $frame instanceof ErrorFrame ? $frame: throw new \TypeError("Expected ErrorFrame");



                $this->assertEquals(0, $errorFrame->streamId());
                $this->assertEquals(ErrorType::INVALID_SETUP, $errorFrame->type());
                $this->assertEquals("Version not supported", $errorFrame->errorMesage());
                $resolver(true);
            });
        });

        $buffer = new ArrayBuffer();
        $buffer->addUInt32(0);
        $buffer->addUInt16(1024);
        $buffer->addUInt16(0);
        $buffer->addUInt16(1);
        $buffer->addUInt32(60000);
        $buffer->addUInt32(300000);
        $setupFrame = $buffer->toString();
        $setupFrame .= chr(24);
        $setupFrame .= "application/octet-stream";
        $setupFrame .= chr(24);
        $setupFrame .= "application/octet-stream";

        $this->testClient->write($setupFrame);

        await(timeout($expectedErrorFrame,self::TIMEOUT));
        $this->assertCount(0, $this->server->getConnections());
    }

    protected function tearDown():void
    {
        parent::tearDown();
        $this->server?->close();
    }
}
