<?php

declare(strict_types=1);

namespace App\Connection\Builder;

use App\Connection\Client\IRSocketClient;
use App\Connection\Server\TCPServer;
use App\Core\Enums\ConnectionType;
use App\Server\IRSocketServer;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;

interface IConnectionBuilder
{
    public function setAddress(string $address): self;

    public function setUrl(string $url): self;

    public function setConnectionType(ConnectionType $type): self;

    public function setPort(string $port): self;

    public function setLoop(?LoopInterface $loop): self;

    public function setTimeout(int|bool $timeout): self;

    public function setDns(string|bool $dns): self;

    public function setDnsResolver(?ResolverInterface $dnsResolver): self;

    /** @phpstan-ignore-next-line */
    public function setTlsOptions(array $tlsOption): self;

    /** @phpstan-ignore-next-line */
    public function setSocketOptions(array $tlsOption): self;

    public function createClient(): IRSocketClient;

    public function createServer(): TCPServer;
}
