<?php

declare(strict_types=1);

namespace App\Connection\Builder;

use App\Connection\Client\IRSocketClient;
use App\Connection\Server\IRSocketServer;
use App\Core\Enums\ConnectionType;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectorInterface;

/**
 * @phpstan-type contextArray = array{
 * tcp?: mixed,
 * tls?: mixed,
 * unix?: mixed,
 * dns?: mixed,
 * timeout?: mixed,
 * happy_eyeballs?: mixed
 * }
 */
interface IConnectionBuilder
{
    public function setAddress(string $address): self;

    public function setUrl(Url $url): self;

    public function setConnectionType(ConnectionType $type): self;

    public function setPort(string $port): self;

    public function setLoop(?LoopInterface $loop): self;

    public function setTimeout(int $timeout): self;

    public function setDnsAddress(string $dnsAddress): self;

    public function setDnsUrl(Url $dnsUrl): self;

    public function setDnsResolver(?ResolverInterface $dnsResolver): self;

    /**
     * @param array<mixed> $tlsOption
     */
    public function setTlsOptions(array $tlsOption): self;

    /**
     * @param contextArray $socketOption
     */
    public function setSocketOptions(array $socketOption): self;

    public function setFrameFactory(IFrameFactory $factory): self;

    /**
     * @param array<mixed> $subProtocols
     */
    public function setSubProtocols(array $subProtocols): self;

    /**
     * @param array<mixed> $headers
     */
    public function setHeaders(array $headers): self;

    public function setConnector(ConnectorInterface $connector): self;

    public function createClient(): IRSocketClient;

    public function createServer(): IRSocketServer;
}
