<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\RSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
use Exception;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

final class TCPClient implements IRSocketClient
{
    private IFrameFactory $frameFactory;

    public function __construct(private readonly ConnectorInterface $connector, private readonly Url $url, IFrameFactory $frameFactory)
    {
        $this->frameFactory = $frameFactory;
    }

    public function connect(ConnectionSettings $settings = new ConnectionSettings(), DataDTO $data = null, DataDTO $metaData = null): Promise
    {
        $setupFrame = SetupFrame::fromSettings($settings);
        if ($data) {
            $setupFrame = $setupFrame->setData($data);
        }

        if ($metaData) {
            $setupFrame = $setupFrame->setMetaData($metaData);
        }

        return new Promise(function (callable $resolver, callable $reject) use ($setupFrame): void {
            $this->connector->connect($this->url->getAddress())
                ->then(function (ConnectionInterface $connection) use ($resolver, $setupFrame): void {
                    $value = $setupFrame->serialize();
                    $sizeBuffer = new ArrayBuffer();
                    $sizeBuffer->addUInt24(strlen($value));
                    $connection->write($sizeBuffer->toString().$value);
                    $resolver(new RSocketConnection($connection, $this->frameFactory));
                }, function (Exception $e) use ($reject): void {
                    var_dump($e);
                    // todo lepsze exceptiony
                    $reject($e);
                });
        });
    }
}
