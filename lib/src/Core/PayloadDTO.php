<?php

declare(strict_types=1);

namespace App\Core;

class PayloadDTO
{
    public function __construct(
        public readonly string $data,
        public readonly ?string $mimeData = null,
    ) {
    }
}
