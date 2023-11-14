<?php

declare(strict_types=1);

namespace App\Frame;

class PayloadFrame
{
    private int $streamId;
    private string $payload;
    private bool $hasMetadata;
    private bool $follows;
    private bool $complete;
    private bool $next;

    public function __construct(
        int $streamId,
        string $payload,
        bool $hasMetadata,
        bool $follows,
        bool $complete,
        bool $next,
    ) {
        $this->streamId = $streamId;
        $this->payload = $payload;
        $this->hasMetadata = $hasMetadata;
        $this->follows = $follows;
        $this->complete = $complete;
        $this->next = $next;
    }

    public function fromString(string $data): void
    {
    }

    public function serialize(): string
    {
        return '';
    }

    public function streamId(): int
    {
        return $this->streamId;
    }

    public function complete(): bool
    {
        return $this->complete;
    }

    public function next(): bool
    {
        return $this->next;
    }

    public function payload(): string
    {
        return $this->payload;
    }
}
