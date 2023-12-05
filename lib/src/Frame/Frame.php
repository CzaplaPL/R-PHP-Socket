<?php

declare(strict_types=1);

namespace App\Frame;

abstract class Frame
{
    public const SETUP_STREAM_ID = 0;
    public const MAJOR_VERSION = 1;
    public const MINOR_VERSION = 0;

    public function __construct(protected readonly int $streamId)
    {
    }

    abstract public function serialize(): string;

    public function streamId(): int
    {
        return $this->streamId;
    }
}
