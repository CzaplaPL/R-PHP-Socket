<?php

declare(strict_types=1);

namespace App\Connection\Server;

use App\Connection\RSocketConnection;
use Rx\Observable;

interface IRSocketServer
{
    public function bind(ServerSettings $settings = new ServerSettings(), callable $errorHandler = null): void;

    /**
     * @return RSocketConnection[]
     */
    public function getConnections(): array;

    public function close(): void;

    public function newConnections(): Observable;

    public function closedConnections(): Observable;
}
