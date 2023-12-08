<?php

namespace App\Tests\Connection;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\Client\TCPClient;
use App\Connection\RSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\ConnectionFailedException;
use App\Core\PayloadDTO;
use App\Core\Url;
use App\Frame\FireAndForgetFrame;
use App\Frame\PayloadFrame;
use App\Frame\RequestResponseFrame;
use App\Frame\SetupFrame;
use App\Tests\Extensions\TestConnection;
use App\Tests\Extensions\TestConnector;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use Rx\Subject\Subject;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class RequestResponseTest extends RSocketTestCase
{
    public function testSendRequestResponse(): void
    {
        $address = '127.0.0.1:9090';

        $testConnector = $this->getTestConnector();
        $testConnector->setUrl($address);

        $data = 'dane';
        $frame = new RequestResponseFrame(
            2,
            $data,
        );

        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));

        $testConnector->expectedSendData($sizeBuffer->toString() . $value);

        $client = (new ConnectionBuilder($address))
            ->setConnector($testConnector)
            ->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(), self::TIMEOUT));

        $connection->connect();


        $connection->requestResponse('dane');

        $connection->close();
    }

    public function testReciveRequestResponse(): void
    {
        $testConnector = $this->getTestConnector();

        $client = (new ConnectionBuilder())
            ->setConnector($testConnector)
            ->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(), self::TIMEOUT));
        $connection->connect();

        $frame = new PayloadFrame(
            streamId: 2,
            data: 'response',
            complete: true
        );

        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));
        $testConnector->expectedSendData($sizeBuffer->toString() . $value);


        $reciveRequestResponse = new Promise(function (callable $resolve) use ($connection) {
            $connection->onRecivedRequest()->take(1)->subscribe(function (RequestResponseFrame $frame) use ($resolve, $connection) {
                $this->assertEquals('data', $frame->getData());
                $connection->sendResponse(streamId: $frame->streamId(),data: 'response',complete: true);
                $resolve(true);
            });
        });

        $frame = new RequestResponseFrame(
            streamId: 2,
            data: 'data'
        );

        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));


        $testConnector->send($sizeBuffer->toString() . $value);
        await(timeout($reciveRequestResponse, self::TIMEOUT));
        $connection->close();
    }
}
