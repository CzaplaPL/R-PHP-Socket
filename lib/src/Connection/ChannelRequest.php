<?php

declare(strict_types=1);

namespace App\Connection;

use Rx\Observable;

class ChannelRequest
{
    public function __construct(
        public readonly int $streamId,
        public readonly Observable $response
    ) {
    }
}
