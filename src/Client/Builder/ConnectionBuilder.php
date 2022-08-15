<?php

declare(strict_types=1);

namespace App\Client\Builder;

use App\Client\Connection;
use App\Client\IRSocketClient;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\WrongUrlException;
use App\Core\Url;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;

final class ConnectionBuilder implements IConnectionBuilder
{
    private Url $url;

    /**
     * @throws WrongUrlException
     */
    public function __construct(string $address = '')
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

    public function setTlsOptions(array $tlsOption): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setTlsOptions() method.
    }

    public function setSocketOptions(array $tlsOption): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setSocketOptions() method.
    }

    public function setHttpHeader(array $httpHeader): IConnectionBuilder
    {
        return $this;
        // TODO: Implement setHttpHeader() method.
    }

    public function connect(): IRSocketClient
    {
        return new Connection();
        // TODO: Implement connect() method.
    }
}
