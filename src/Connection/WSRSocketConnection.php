<?php

declare(strict_types=1);

namespace App\Connection;

class WSRSocketConnection extends RSocketConnection
{
    protected function decodeFrames(string $data): iterable
    {
        yield $this->frameFactory->create($data);
    }
}
