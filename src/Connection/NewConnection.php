<?php

declare(strict_types=1);

namespace App\Connection;

use App\Frame\SetupFrame;

class NewConnection
{
    public function __construct(
        public readonly RSocketConnection $connection,
        public readonly SetupFrame $frame
    ) {
    }
}
