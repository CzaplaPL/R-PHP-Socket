<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use App\Connection\Server\IRSocketServer;
use App\Connection\Server\ServerSettings;
use App\Tests\Extensions\Constraint\ExpectedSendFrame;
use PHPUnit\Framework\Constraint\Constraint;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Rx\Observable;
use Rx\Subject\Subject;

/**
 * @internal
 */
final class TCPTestServer implements IRSocketServer
{
    /**
     * @var string[] $expectedConnectionsAddress
     */
    private array $expectedConnectionsAddress = [];
    /**
     * @var string[] $connectedAddresses
     */
    private array $connectedAddresses = [];

    /**
     * @var ConnectionInterface[] $connections
     */
    private array $connections = [];

    private ?SocketServer $socket = null;

    public function __construct(private readonly string $adddres)
    {
    }


    public function expectConnectionFromAddress(string $address): void
    {
        $this->expectedConnectionsAddress[] = $address;
    }

    /**
     * @return array<mixed,Constraint>
     */
    public function getConstraints(): array
    {
        $constraints = [];
        foreach ($this->expectedConnectionsAddress as $address) {
            $constraints[$address] = new ExpectedSendFrame($this->connectedAddresses);
        }

        return $constraints;
    }

    public function close(): void
    {
        $this->socket?->removeAllListeners();
        $this->socket?->close();
    }

    public function reset(): void
    {
        $this->socket?->removeAllListeners();
        $this->socket?->close();
        $this->connectedAddresses = [];
        $this->expectedConnectionsAddress = [];
        $this->createSocket();
    }

    private function createSocket(): void
    {
        $this->socket = new SocketServer($this->adddres);

        $this->socket->on('connection', function (ConnectionInterface $connection): void {
            $this->connections[] =$connection;
            if($connection->getRemoteAddress()) {
                $this->connectedAddresses[] = $connection->getRemoteAddress();
            }
        });
    }

    public function bind(ServerSettings $settings = new ServerSettings(), ?callable $errorHandler = null): void
    {
        $this->createSocket();
    }

    /**
     * @return ConnectionInterface[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    public function newConnections(): Observable
    {
        return new Subject();
    }

    public function closedConnections(): Observable
    {
        return new Subject();
    }


}
