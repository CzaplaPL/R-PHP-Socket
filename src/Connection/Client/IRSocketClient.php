<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Core\DataDTO;
use React\Promise\Promise;

interface IRSocketClient
{
    public function connect(
        ConnectionSettings $settings = new ConnectionSettings(),
        ?DataDTO           $data = null,
        ?DataDTO           $metaData = null
    ): Promise;
}
