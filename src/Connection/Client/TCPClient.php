<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\RSocketConnection;
use App\Core\Url;
use App\Frame\SetupFrame;
use Exception;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

final class TCPClient implements IRSocketClient
{
    public function __construct(private readonly Connector $connector, private readonly Url $url)
    {
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
                    $resolver(new RSocketConnection($connection ));
                }, function (Exception $e) use ($reject): void {
                    // todo lepsze exceptiony
                    $reject($e);
                });
        });
    }
}
