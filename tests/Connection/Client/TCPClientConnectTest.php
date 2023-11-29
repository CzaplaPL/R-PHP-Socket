<?php

namespace App\Tests\Connection\Client;

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\Client\TCPClient;
use App\Connection\RSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Url;
use App\Frame\SetupFrame;
use App\Tests\Extensions\TestConnector;
use App\Tests\RSocketTestCase;
use PHPUnit\Framework\TestCase;
use function React\Async\await;
use function React\Promise\Timer\timeout;

class TCPClientConnectTest extends RSocketTestCase
{
    public function testConnectionRejectWhenServerNotAvailable(): void
    {
        $connectionBuilder = new ConnectionBuilder(self::TCP_ADDRESS);
        $client = $connectionBuilder->createClient();

        $this->expectException(ConnectionFailedException::class);

        await($client->connect());
    }

    public function testSendDefaultSetupFrameOnConnect(): void
    {
        $address = '127.0.0.1:9090';

        $testConnector = $this->getTestConnector();
        $testConnector->setUrl($address);

        $value = (new SetupFrame())->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));

        $testConnector->expectedSendData($sizeBuffer->toString() . $value);

        $client = (new ConnectionBuilder($address))
            ->setConnector($testConnector)
            ->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(),self::TIMEOUT));

        $connection->connect();
    }

    public function testSendConfigureSetupFrameOnConnect(): void
    {
        $address = 'tls://22.0.0.1:90';

        $testConnector = $this->getTestConnector();
        $testConnector->setUrl($address);

        $settings = (new ConnectionSettings())
            ->setKeepAlive(2000)
            ->setLifetime(300)
            ->setReasumeEnable(true)
            ->setLeaseEnable(true)
            ->setReasumeToken('token');

        $data = new DataDTO('data' , 'type');
        $metaData = new DataDTO('meta data' , 'type');

        $setupFrame = SetupFrame::fromSettings($settings);
        $setupFrame = $setupFrame->setData($data);
        $setupFrame = $setupFrame->setMetaData($metaData);

        $value = $setupFrame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));

        $testConnector->expectedSendData($sizeBuffer->toString() . $value);

        $client = (new ConnectionBuilder())
            ->setAddress('22.0.0.1')
            ->setPort('90')
            ->setConnectionType(ConnectionType::TLS)
            ->setConnector($testConnector)
            ->createClient();

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(),self::TIMEOUT));

        $connection->connect( $settings, $data, $metaData);


    }

    public function testThrowExceptionWhenErrorOnSendSetupFrameOnConnect(): void
    {
        $testConnector = $this->getTestConnector();
        $testConnector->throwErrorOnSendData();

        $client = (new ConnectionBuilder())
            ->setConnector($testConnector)
            ->createClient();

        $this->expectException(ConnectionFailedException::class);

        /**
         * @var RSocketConnection $connection
         */
        $connection = await(timeout($client->connect(),self::TIMEOUT));

        $connection->connect();
    }
}
