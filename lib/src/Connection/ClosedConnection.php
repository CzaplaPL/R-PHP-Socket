<?php

declare(strict_types=1);

namespace App\Connection;

use App\Core\Exception\ConnectionErrorException;

class ClosedConnection
{
    public function __construct(
        public readonly RSocketConnection $connection,
        public readonly ?ConnectionErrorException $errorException = null,
    ) {
    }
}
