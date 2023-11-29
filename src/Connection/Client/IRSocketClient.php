<?php

declare(strict_types=1);

namespace App\Connection\Client;

use React\Promise\PromiseInterface;

interface IRSocketClient
{
    public function connect(): PromiseInterface;
}
