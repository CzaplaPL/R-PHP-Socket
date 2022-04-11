<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Enums\ConnectionType;
use JetBrains\PhpStorm\Pure;

final class Url
{
    public function __construct(
        private string $url,
        private ConnectionType $connectionType,
        private string $port
    ) {
    }

    #[Pure]
    public function getAddress(): string
    {
        return sprintf('%s%s%s', $this->connectionType->value, $this->url, $this->port);
    }
}
