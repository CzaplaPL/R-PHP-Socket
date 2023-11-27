<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use App\Connection\Client\ConnectionSettings;
use App\Connection\Client\IRSocketClient;
use App\Connection\Server\IRSocketServer;
use App\Connection\WSRSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Frame\SetupFrame;
use App\Tests\Extensions\Constraint\ExpectedSendFrame;
use PHPUnit\Framework\Constraint\Constraint;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\SocketServer;
use Throwable;

/**
 * @internal
 */
final class TCPTestClient
{
    private ?ConnectionInterface $connection = null;

    public function connect(string $url): PromiseInterface
    {
        return new Promise(function (callable $resolver, callable $reject) use ($url): void {
            $conector =new Connector();
            $conector->connect($url)
                ->then(
                    onFulfilled: function (ConnectionInterface $connection) use ($resolver, $reject): void {
                        try {
                            $this->connection = $connection;
                            $resolver($connection);
                        } catch (Throwable $error) {
                            $reject($error);
                        }
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject($error);
                    }
                );
        });
    }

    public function SendSetupFrame(
        ConnectionSettings $settings = new ConnectionSettings(),
        DataDTO $data = null, DataDTO $metaData = null
    ): void{
        if($this->connection === null) {
            throw new \Exception("Client Not Connected");
        }
        $setupFrame = SetupFrame::fromSettings($settings);

        if ($data) {
            $setupFrame = $setupFrame->setData($data);
        }
        if ($metaData) {
            $setupFrame = $setupFrame->setMetaData($metaData);
        }

        $this->write($setupFrame->serialize());
    }

    private function write(string $data): void
    {
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($data));
        if(is_null($this->connection)){
            throw new \Exception("connect To server before send data");
        }
        $this->connection->write($sizeBuffer->toString().$data);
    }

    public function close()
    {
        $this->connection?->close();
    }

}
