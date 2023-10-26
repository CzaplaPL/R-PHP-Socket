<?php

declare(strict_types=1);

namespace App\Connection;

use React\Socket\ConnectionInterface;
use Rx\Observable;

interface IRSocketConnection
{
    public function getLocalAddress(): ?string;

    public function requestResponse(string $data): Observable;

    public function conection(): ConnectionInterface;
}
