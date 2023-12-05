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
}
