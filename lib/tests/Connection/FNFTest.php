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
use App\Core\Url;
use App\Frame\FireAndForgetFrame;
use App\Frame\SetupFrame;
use App\Tests\Extensions\TestConnection;
use App\Tests\Extensions\TestConnector;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class FNFTest extends RSocketTestCase
{


    public function testSendFnF(): void
    {
        $address = '127.0.0.1:9090';

        $testConnector = $this->getTestConnector();
        $testConnector->setUrl($address);

        $data = 'dane';
        $metaData = 'meta-dane';
        $frame = new FireAndForgetFrame(
            2,
            $data,
            $metaData
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

        $connection->fireAndForget($data, $metaData);
        $connection->close();
    }


    public function testReciveFnF(): void
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
        $reciveFNF = new Promise(function (callable $resolve) use ($connection) {
            $connection->onRecivedRequest()->take(1)->subscribe(function (FireAndForgetFrame $frame) use ($resolve) {
                $this->assertEquals('data', $frame->getData());
                $resolve(true);
            });
        });

        $frame = new FireAndForgetFrame(
            streamId: 2,
            data: 'data'
        );

        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));


        $testConnector->send($sizeBuffer->toString() . $value);
        await(timeout($reciveFNF, self::TIMEOUT));

        $connection->close();
    }
}
