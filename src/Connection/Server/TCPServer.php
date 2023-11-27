<?php

declare(strict_types=1);

namespace App\Connection\Server;

use App\Connection\RSocketConnection;
use App\Connection\TCPRSocketConnection;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\Frame;
use App\Frame\SetupFrame;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Socket\SocketServer;
use Rx\Observable;
use Rx\Subject\Subject;

final class TCPServer implements IRSocketServer
{
    private ?ServerInterface $server = null;
    private Subject $newConnectionsSubject;
    private Subject $closedConnectionsSubject;
    /**
     * @var RSocketConnection[]
     */
    private array $connections = [];

    private ServerSettings $settings;

    public function __construct(private readonly Url $url, private readonly IFrameFactory $frameFactory)
    {
        $this->newConnectionsSubject = new Subject();
        $this->closedConnectionsSubject = new Subject();
        $this->settings = new ServerSettings();
    }

    public function bind(ServerSettings $settings = new ServerSettings(), callable $errorHandler = null): void
    {
        $this->settings = $settings;
        $this->server = new SocketServer($this->url->getAddress());
        $this->server->on('connection', function (ConnectionInterface $connection): void {
            $newConection = new TCPRSocketConnection($connection, $this->frameFactory);
            $newConection->addLissener(RSocketConnection::SETUP_LISSENER_KEY, $this->akceptConnection(...));
            $newConection->addLissener(RSocketConnection::SETUPED_LISSENER_KEY, $this->successfullConnected(...));
            $newConection->addLissener(RSocketConnection::CLOSE_LISSENER_KEY, $this->closeConnection(...));
        });
        if ($errorHandler) {
            $this->server->on('error', $errorHandler);
        }
    }

    /**
     * @return RSocketConnection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    public function close(): void
    {
        $this->server?->close();
    }

    public function getConnection(string $id): ?RSocketConnection
    {
        return $this->connections[$id] ?? null;
    }

    public function newConnections(): Observable
    {
        return $this->newConnectionsSubject->asObservable();
    }

    public function closedConnections(): Observable
    {
        return $this->closedConnectionsSubject->asObservable();
    }

    public function addErrorHandler(callable $errorHandle): void
    {
        $this->server?->on('error', $errorHandle);
    }

    /**
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedInferredReturnType
     *
     * @return callable[]
     */
    public function getAllErrorHandler(): array
    {
        return $this->server ? $this->server->listeners('error') : [];
    }

    public function resetErrorHandler(callable $errorHandler = null): void
    {
        $this->server?->removeAllListeners('error');

        if ($errorHandler) {
            $this->server?->on('error', $errorHandler);
        }
    }

    private function akceptConnection(SetupFrame $frame, RSocketConnection $connection): ?Frame
    {
        $this->connections[] = $connection;

        return null;
    }

    private function successfullConnected(RSocketConnection $connection): void
    {
        $this->newConnectionsSubject->onNext($connection);
    }

    private function closeConnection(RSocketConnection $connection): void
    {
        $this->closedConnectionsSubject->onNext($connection);
    }
}
