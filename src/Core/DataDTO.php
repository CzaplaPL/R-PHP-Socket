<?php

declare(strict_types=1);

namespace App\Core;

class DataDTO
{
    public function __construct(
        private readonly string $data,
        private readonly string $mimeType = 'application/octet-stream',
    ) {
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
