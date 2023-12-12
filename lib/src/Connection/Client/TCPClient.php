<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\TCPRSocketConnection;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use Exception;
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
    private ?string $token = null;

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
                        $this->token = Uuid::uuid4()->toString();
                        $resolver(new TCPRSocketConnection($this->token, $connection, $this->frameFactory));
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject(ConnectionFailedException::errorOnConnecting($this->url->getAddress(), $error));
                    }
                );
        });
    }

    public function reasume(): PromiseInterface
    {
        return new Promise(function (callable $resolver, callable $reject): void {
            if (!$this->token) {
                $reject(new Exception());
            }
            $this->connector->connect($this->url->getAddress())
                ->then(
                    onFulfilled: function (ConnectionInterface $connection) use ($resolver): void {
                        $connection = new TCPRSocketConnection($this->token, $connection, $this->frameFactory);
                        $connection->reasume();
                        $resolver($connection);
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject(ConnectionFailedException::errorOnConnecting($this->url->getAddress(), $error));
                    }
                );
        });
    }
}
