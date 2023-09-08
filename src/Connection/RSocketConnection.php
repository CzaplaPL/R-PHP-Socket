<?php

declare(strict_types=1);

namespace App\Connection;

use React\Socket\ConnectionInterface;

final class RSocketConnection implements IRSocketConnection
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    public function getLocalAddress(): ?string
    {
        return $this->connection->getLocalAddress();
    }

    public function conection(): ConnectionInterface {
        return $this->connection;
    }
}
