<?php

declare(strict_types=1);

namespace App\Connection\Server;

use App\Connection\ClosedConnection;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Connection\TCPRSocketConnection;
use App\Core\Exception\ServerErrorException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Ramsey\Uuid\Uuid;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Socket\SocketServer;
use Rx\Observable;
use Rx\Subject\Subject;

final class TCPServer implements IRSocketServer
{
    private ?ServerInterface $server = null;
    private Subject $subscriptions;
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
        $this->subscriptions = new Subject();
    }

    public function bind(ServerSettings $settings = new ServerSettings(), callable $errorHandler = null): void
    {
        if ($this->server) {
            throw ServerErrorException::ServerAlreadyBinding();
        }

        $this->settings = $settings;
        $this->server = new SocketServer($this->url->getAddress());
        $this->server->on('connection', function (ConnectionInterface $connection): void {
            $id = Uuid::uuid4();
            $newConection = new TCPRSocketConnection($id, $connection, $this->frameFactory, $this->settings);
            $newConection->onConnect()->takeUntil($this->subscriptions)->subscribe(function (SetupFrame $setupFrame) use ($newConection): void {
                $this->newConnectionsSubject->onNext(new NewConnection($newConection, $setupFrame));
            });
            $newConection->onClose()->takeUntil($this->subscriptions)->subscribe(
                function (ClosedConnection $closedConnection): void {
                    if (false === $closedConnection->connection->isReasumeEnable()) {
                        unset($this->connections[$closedConnection->connection->id->toString()]);
                    }
                    $this->closedConnectionsSubject->onNext($closedConnection);
                }
            );
            $this->connections[$id->toString()] = $newConection;
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
        foreach ($this->connections as $connection) {
            $connection->close();
        }

        $this->server?->close();

        $this->server = null;
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

    public function __destruct()
    {
        $this->subscriptions->onCompleted();
    }
}
