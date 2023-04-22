<?php

declare(strict_types=1);

namespace App\Connection\Builder;

use App\Connection\Client\IRSocketClient;
use App\Connection\Client\TCPClient;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\WrongUrlException;
use App\Core\Url;
use App\Server\IRSocketServer;
use App\Tests\Extensions\TestServer;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Connector;

final class ConnectionBuilder implements IConnectionBuilder
{
    private Url $url;

    /**
     * @throws WrongUrlException
     */
    public function __construct(string $address = '127.0.0.1:9090')
    {
        $this->url = Url::fromAddress($address);
    }

    public function setAddress(string $address): IConnectionBuilder
    {
        $this->url = Url::fromAddress($address);

        return $this;
    }

    public function setUrl(string $url): IConnectionBuilder
    {
        $this->url = $this->url->setUrl($url);

        return $this;
    }

    public function setConnectionType(ConnectionType $type): IConnectionBuilder
    {
        $this->url = $this->url->setType($type);

        return $this;
    }

    public function setPort(string $port): IConnectionBuilder
    {
        $this->url = $this->url->setPort($port);

        return $this;
    }

    public function setLoop(?LoopInterface $loop): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setLoop() method.
    }

    public function setTimeout(bool|int $timeout): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setTimeout() method.
    }

    public function setDns(bool|string $dns): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setDns() method.
    }

    public function setDnsResolver(?ResolverInterface $dnsResolver): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setDnsResolver() method.
    }

    /** @phpstan-ignore-next-line */
    public function setTlsOptions(array $tlsOption): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setTlsOptions() method.
    }

    /** @phpstan-ignore-next-line */
    public function setSocketOptions(array $tlsOption): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setSocketOptions() method.
    }

    public function createClient(): IRSocketClient
    {
        $connector = new Connector();

        return new TCPClient($connector, $this->url);
    }

    public function createServer(): IRSocketServer
    {
        return new TestServer();
    }
}
