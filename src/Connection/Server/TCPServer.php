<?php

declare(strict_types=1);

namespace App\Connection\Server;

use App\Connection\RSocketConnection;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Exception;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\ServerInterface;
use React\Socket\SocketServer;

final class TCPServer
{
    private ServerInterface $server;
    /**
     * @var ConnectionInterface[]
     */
    private array $connections;

    public function __construct(private readonly Url $url, private readonly  IFrameFactory $frameFactory)
    {
        $this->server = new SocketServer($this->url->getAddress());
        $this->server->on('connection', function (ConnectionInterface $connection) {
          $this->connections[] = new RSocketConnection($connection, $this->frameFactory);
        });
    }
}
