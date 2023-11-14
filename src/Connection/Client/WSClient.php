<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\RSocketConnection;
use App\Core\DataDTO;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Ratchet\Client\Connector;
use React\Promise\Promise;

final class WSClient implements IRSocketClient
{
    public function __construct(
        private readonly Url $url,
        private readonly IFrameFactory $frameFactory,
        private readonly Connector $connector,
        private readonly array $subProtocols,
        private readonly array $headers
    ) {
    }

    public function connect(
        ConnectionSettings $settings = new ConnectionSettings(),
        DataDTO $data = null,
        DataDTO $metaData = null
    ): Promise {
        return new Promise(function (callable $resolver, callable $reject) use ($settings): void {
            $connector = $this->connector;
            $connector($this->url->getAddress(), $this->subProtocols, $this->headers)->then(function ($connection) use ($resolver, $settings): void {
                $setupFrame = SetupFrame::fromSettings($settings);
                $value = $setupFrame->serialize();
                $connection->write($value);
                $resolver(new RSocketConnection($connection, $this->frameFactory));
            }, function ($e) use ($reject): void {
                $reject($e);
            });
        });
    }
}
