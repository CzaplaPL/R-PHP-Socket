<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\RSocketConnection;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Exception;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

final class TCPClient implements IRSocketClient
{
    private IFrameFactory $frameFactory;
    public function __construct(private readonly Connector $connector, private readonly Url $url,  IFrameFactory $frameFactory)
    {
        $this->frameFactory = $frameFactory;
    }

    public function connect(): Promise
    {

        $setupFrame = new SetupFrame();


        return new Promise(function (callable $resolver, callable $reject) use ($setupFrame) : void {
            $this->connector->connect($this->url->getAddress())
                ->then(function (ConnectionInterface $connection) use ($resolver, $setupFrame): void {
                    $value = $setupFrame->serialize();
                    var_dump($value);
                    $connection->write($value);
                    $resolver(new RSocketConnection($connection, $this->frameFactory ));
                }, function (Exception $e) use ($reject): void {
                    var_dump($e);
                    // todo lepsze exceptiony
                    $reject($e);
                });
        });
    }
}
