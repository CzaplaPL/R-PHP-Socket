<?php

declare(strict_types=1);

namespace App\Connection;

interface IRSocketConnection
{
    public function getLocalAddress(): ?string;
}
