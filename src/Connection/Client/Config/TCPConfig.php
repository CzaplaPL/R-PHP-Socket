<?php

declare(strict_types=1);

namespace App\Connection\Client\Config;

use App\Core\Url;
use JetBrains\PhpStorm\Pure;

final class TCPConfig
{
    private Url $url;

    #[Pure]
    public function __construct()
    {
        $this->url = new Url('url');
    }

    #[Pure]
    public function getUrl(): string
    {
        return $this->url->getAddress();
    }

    /**
     * @return array<string>
     */
    #[Pure]
    public function getOptions(): array
    {
        return [];
    }
}
