<?php

declare(strict_types=1);

namespace App\Test;

use App\Server\IRSocketServer;
use App\Test\Constraint\ExpectedAddressConstraint;
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
        $this->socket = new SocketServer('127.0.0.1:80');

        $this->socket->on('connection', function (ConnectionInterface $connection): void {
            $this->connectedAddresses[] = $connection->getRemoteAddress();
            //            $connection->write("Hello " . $connection->getRemoteAddress() . "!\n");
            //            $connection->write("Welcome to this amazing server!\n");
            //            $connection->write("Here's a tip: don't say anything.\n");
            //
            //            $connection->on('data', function ($data) use ($connection) {
            //                $connection->close();
            //            });
        });
    }
}
