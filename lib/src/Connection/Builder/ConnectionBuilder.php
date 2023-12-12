<?php

declare(strict_types=1);

namespace App\Connection\Builder;

use App\Connection\Client\IRSocketClient;
use App\Connection\Client\TCPClient;
use App\Connection\Client\WSClient;
use App\Connection\Server\IRSocketServer;
use App\Connection\Server\TCPServer;
use App\Core\Enums\ConnectionType;
use App\Core\Url;
use App\Frame\Factory\FrameFactory;
use App\Frame\Factory\IFrameFactory;
use Ratchet\Client\Connector as WSConnector;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;

/**
 * @phpstan-import-type contextArray from IConnectionBuilder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ConnectionBuilder implements IConnectionBuilder
{
    private Url $url;
    private ?Url $dnsUrl = null;

    private ?LoopInterface $loop = null;

    private ?ConnectorInterface $connector = null;

    private ?int $timeout = null;

    private ?ResolverInterface $dnsResolver = null;

    /**
     * @var array<mixed>
     */
    private array $tlsOption = [];

    /**
     * @var array<mixed>
     */
    private array $subProtocols = [];

    /**
     * @var array<mixed>
     */
    private array $headers = [];

    /**
     * @var contextArray
     */
    private array $socketOption = [];

    private IFrameFactory $frameFactory;

    public function __construct(string $address = '127.0.0.1:9090')
    {
        $this->url = Url::fromAddress($address);
        $this->frameFactory = new FrameFactory();
    }

    public function setAddress(string $address): IConnectionBuilder
    {
        $this->url = Url::fromAddress($address);

        return $this;
    }

    public function setUrl(Url $url): IConnectionBuilder
    {
        $this->url = $url;

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
        $this->loop = $loop;

        return $this;
    }

    public function setTimeout(?int $timeout): IConnectionBuilder
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setDnsAddress(string $dnsAddress): IConnectionBuilder
    {
        $this->dnsUrl = Url::fromAddress($dnsAddress);

        return $this;
    }

    public function setDnsUrl(Url $dnsUrl): IConnectionBuilder
    {
        $this->dnsUrl = $dnsUrl;

        return $this;
    }

    public function setDnsResolver(?ResolverInterface $dnsResolver): IConnectionBuilder
    {
        $this->dnsResolver = $dnsResolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTlsOptions(array $tlsOption): IConnectionBuilder
    {
        $this->tlsOption = $tlsOption;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSocketOptions(array $socketOption): IConnectionBuilder
    {
        $this->socketOption = $socketOption;

        return $this;
    }

    public function setFrameFactory(IFrameFactory $factory): IConnectionBuilder
    {
        $this->frameFactory = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubProtocols(array $subProtocols): IConnectionBuilder
    {
        $this->subProtocols = $subProtocols;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers): IConnectionBuilder
    {
        $this->headers = $headers;

        return $this;
    }

    public function setConnector(ConnectorInterface $connector): IConnectionBuilder
    {
        $this->connector = $connector;

        return $this;
    }

    public function createClient(): IRSocketClient
    {
        if (!$this->connector) {
            $this->connector = new Connector($this->createContextArray(), $this->loop);
        }

        if (ConnectionType::WS === $this->url->getConnectionType()) {
            $connector = new WSConnector($this->loop, $this->connector);

            return new WSClient($this->url, $this->frameFactory, $connector, $this->subProtocols, $this->headers);
        }

        return new TCPClient($this->connector, $this->url, $this->frameFactory);
    }

    public function createServer(): IRSocketServer
    {
        return new TCPServer($this->url, $this->frameFactory);
    }

    /**
     * @return contextArray
     */
    private function createContextArray(): array
    {
        return $this->socketOption ?: [
            'tls' => $this->tlsOption ?: true,
            'dns' => $this->dnsResolver ?: ($this->dnsUrl ? $this->dnsUrl->getAddress() : true),
            'timeout' => is_null($this->timeout) ? true : $this->timeout,
        ];
    }
}
