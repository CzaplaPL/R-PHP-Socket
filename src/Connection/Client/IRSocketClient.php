<?php

declare(strict_types=1);

namespace App\Connection\Client;

use React\Promise\Promise;

interface IRSocketClient
{
    public function connect(): Promise;
}
