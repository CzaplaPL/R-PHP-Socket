<?php

namespace App\Connection;

use App\Frame\LeaseFrame;

class LeaseMenager
{
    public function __construct(
        private int $limit = 0,
        private int $ttl = 0
    )
    {
    }

    public function setNewLimit(LeaseFrame $frame): void
    {
        $this->limit = $frame->limit;
        $this->ttl = time() + $frame->TTL;
    }

    public function canSend(): bool
    {
        if ($this->ttl < time()) {
            return false;
        }

        if ($this->limit <= 0) {
            return false;
        }

        return true;
    }

    public function send(): void
    {
        $this->limit -=1;
    }
}