<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use App\Connection\Server\IRSocketServer;
use App\Tests\Extensions\Constraint\ExpectedAddressConstraint;
use PHPUnit\Framework\Constraint\Constraint;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

/**
 * @internal
 */
final class TestServer implements IRSocketServer
{
    /**
     * @var string[] $expectedConnectionsAddress
     */
    private array $expectedConnectionsAddress = [];
    /**
     * @var string[] $connectedAddresses
     */
    private array $connectedAddresses = [];

    private ?SocketServer $socket = null;

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
            $constraints[$address] = new ExpectedAddressConstraint($this->connectedAddresses);
        }

        return $constraints;
    }

    public function pause(): void
    {
        $this->socket?->pause();
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
        $this->socket = new SocketServer('127.0.0.1:9091');

        $this->socket->on('connection', function (ConnectionInterface $connection): void {
            if($connection->getRemoteAddress()) {
                $this->connectedAddresses[] = $connection->getRemoteAddress();
            }
        });
    }
}
