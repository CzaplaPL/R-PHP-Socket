<?php

declare(strict_types=1);

namespace App\Core;

class PayloadDTO
{
    public function __construct(
        public readonly int $streamId,
        public readonly string $data,
        public readonly ?string $mimeData = null,
    ) {
    }
}
