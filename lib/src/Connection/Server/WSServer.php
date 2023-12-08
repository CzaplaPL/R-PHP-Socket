<?php

declare(strict_types=1);

namespace App\Connection\Server;

use App\Connection\RSocketConnection;
use Rx\Observable;
use Rx\Subject\Subject;

final class WSServer implements IRSocketServer
{
    public function __construct()
    {
    }

    public function bind(ServerSettings $settings = new ServerSettings(), callable $errorHandler = null): void
    {
        // TODO: Implement bind() method.
    }

    public function getConnections(): array
    {
        return [];
    }

    public function close(): void
    {
        // TODO: Implement close() method.
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
