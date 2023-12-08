<?php

declare(strict_types=1);

namespace App\Connection;

use App\Frame\Frame;

class WSRSocketConnection extends RSocketConnection
{
    protected function decodeFrames(string $data): iterable
    {
        yield $this->frameFactory->create($data);
    }

    protected function send(Frame $frame): bool
    {
       return  true;
    }

    protected function end(Frame $frame): void
    {
        // TODO: Implement end() method.
    }
}
