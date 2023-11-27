<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\WSRSocketConnection;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Ratchet\Client\Connector;
use React\Promise\Promise;
use Throwable;

final class WSClient implements IRSocketClient
{
    /**
     * @param mixed[] $subProtocols
     * @param mixed[] $headers
     */
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
        $setupFrame = SetupFrame::fromSettings($settings);

        if ($data) {
            $setupFrame = $setupFrame->setData($data);
        }
        if ($metaData) {
            $setupFrame = $setupFrame->setMetaData($metaData);
        }

        return new Promise(function (callable $resolver, callable $reject) use ($setupFrame): void {
            $connector = $this->connector;
            $connector($this->url->getAddress(), $this->subProtocols, $this->headers)->then(
                onFulfilled: function ($connection) use ($resolver, $reject, $setupFrame): void {
                    try {
                        $connection->send($setupFrame->serialize());
                        $resolver(new WSRSocketConnection($connection, $this->frameFactory, true));
                    } catch (Throwable $error) {
                        $reject(ConnectionFailedException::errorOnSendSetupFrame($error));
                    }
                },
                onRejected: function (Throwable $error) use ($reject): void {
                    $reject(ConnectionFailedException::errorOnConnecting($this->url->getAddress(), $error));
                }
            );
        });
    }
}
