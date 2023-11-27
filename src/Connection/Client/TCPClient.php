<?php

declare(strict_types=1);

namespace App\Connection\Client;

use App\Connection\TCPRSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\Factory\IFrameFactory;
use App\Frame\SetupFrame;
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

    public function connect(ConnectionSettings $settings = new ConnectionSettings(), DataDTO $data = null, DataDTO $metaData = null): PromiseInterface
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
                ->then(
                    onFulfilled: function (ConnectionInterface $connection) use ($resolver, $reject, $setupFrame): void {
                        try {
                            $value = $setupFrame->serialize();
                            $sizeBuffer = new ArrayBuffer();
                            $sizeBuffer->addUInt24(strlen($value));
                            $connection->write($sizeBuffer->toString().$value);
                            $resolver(new TCPRSocketConnection($connection, $this->frameFactory, true));
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
