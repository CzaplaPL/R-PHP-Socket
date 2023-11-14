<?php

declare(strict_types=1);

namespace App\Frame;

abstract class Frame
{
    protected readonly int $majorVersion;
    protected readonly int $minorVersion;

    public function __construct(protected readonly int $streamId)
    {
        $this->majorVersion = 1;
        $this->minorVersion = 0;
    }

    abstract public function serialize(): string;

    public function streamId(): int
    {
        return $this->streamId;
    }
}
