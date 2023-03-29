<?php

declare(strict_types=1);

namespace App\Connection\Client\Config;

final class RSocketConfig
{
    private string $dataMimeType = 'application/octet-stream';
    private string $metadataMimeType = 'application/octet-stream';
    private int $keepAlive = 60000;
    private int $lifetime = 300000;
    private ?string $metadata = null;
    private ?string $data = null;
    private bool $lease = false;
}
