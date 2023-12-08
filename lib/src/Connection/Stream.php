<?php

declare(strict_types=1);

namespace App\Connection;

use Rx\Observable;

class Stream
{
    public function __construct(
        public readonly Observable $data,
        public readonly Observable $requestN,
    ) {
    }
}
