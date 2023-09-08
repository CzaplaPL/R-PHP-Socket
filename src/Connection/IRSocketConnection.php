<?php

declare(strict_types=1);

namespace App\Connection;

use React\Socket\ConnectionInterface;

interface IRSocketConnection
{
    public function getLocalAddress(): ?string;

    public function conection(): ConnectionInterface;
}
