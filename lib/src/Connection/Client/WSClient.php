<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\TCPRSocketConnection;
use App\Connection\WSRSocketConnection;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Ratchet\Client\WebSocket;
use Ratchet\ConnectionInterface;
use React\Promise\PromiseInterface;
use React\Promise\Promise;
use Ramsey\Uuid\Uuid;
use Ratchet\Client\Connector;
use Throwable;

final class WSClient implements IRSocketClient
{

    private ?string $token = null;

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



    public function connect(): PromiseInterface
    {
        return new Promise(function (callable $resolver, callable $reject): void {
            $connector = $this->connector;
            $connector($this->url->getAddress(), $this->subProtocols, $this->headers)
                ->then(
                    onFulfilled: function (WebSocket $connection) use ($resolver): void {
                        $this->token = Uuid::uuid4()->toString();
                        $resolver(new WSRSocketConnection($this->token, $connection, $this->frameFactory));
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject(ConnectionFailedException::errorOnConnecting($this->url->getAddress(), $error));
                    }
                );
        });
    }

    public function reasume(): PromiseInterface{
        return new Promise(function (callable $resolver, callable $reject): void {
            if(!$this->token){
                $reject();
            }
            $connector = $this->connector;
            $connector($this->url->getAddress(), $this->subProtocols, $this->headers)
                ->then(
                    onFulfilled: function (WebSocket $connection) use ($resolver): void {
                        $connection = new WSRSocketConnection($this->token, $connection, $this->frameFactory);
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
