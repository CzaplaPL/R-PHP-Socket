<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\TCPRSocketConnection;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use Ramsey\Uuid\Uuid;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use Throwable;

/**
 * @psalm-suppress TooManyTemplateParams
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class TCPClient implements IRSocketClient
{
    private IFrameFactory $frameFactory;

    public function __construct(private readonly ConnectorInterface $connector, private readonly Url $url, IFrameFactory $frameFactory)
    {
        $this->frameFactory = $frameFactory;
    }

    public function connect(): PromiseInterface
    {
        return new Promise(function (callable $resolver, callable $reject): void {
            $this->connector->connect($this->url->getAddress())
                ->then(
                    onFulfilled: function (ConnectionInterface $connection) use ($resolver): void {
                        $resolver(new TCPRSocketConnection(Uuid::uuid4(), $connection, $this->frameFactory));
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject(ConnectionFailedException::errorOnConnecting($this->url->getAddress(), $error));
                    }
                );
        });
    }
}
