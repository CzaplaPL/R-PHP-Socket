<?php

namespace App\Core;

class DataDTO
{
    public function __construct(
        private readonly string $data,
        private readonly string $mimeType = 'application/octet-stream',
    )
    {
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}