<?php

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Core\PayloadDTO;
use App\Frame\FireAndForgetFrame;
use App\Frame\Frame;
use App\Frame\RequestChannelFrame;
use App\Frame\RequestResponseFrame;
use App\Frame\RequestStreamFrame;
use Generator\BonusesGenerator;
use Generator\ProvisionGenerator;
use React\EventLoop\Loop;
use function React\Async\await;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Generator/ProvisionGenerator.php';

echo 'elo app salary';
$workers = [];

$server = (new ConnectionBuilder())->setAddress('172.26.0.5:9091')->createServer();
$server->bind();

$server->newConnections()->subscribe(function (NewConnection $newConnection) use(&$workers) {
    $newConnection->connection->onRecivedRequest()->subscribe(function (Frame $frame) use ($newConnection, &$workers) {
        if($frame instanceof RequestChannelFrame) {

            $newConnection->connection->sendRequestN($frame->streamId(),50);
            $newConnection->connection->getData($frame->streamId())->subscribe(function (PayloadDTO $payload) use (&$workers){
                $workers[] = json_decode($payload->data,true);
            });
            Loop::get()->addPeriodicTimer(5, function () use (&$workers, $newConnection, $frame) {
                $response = ProvisionGenerator::generate($workers);
                $newConnection->connection->sendResponse($frame->streamId(),json_encode($response));
            });
        }
    });
});



Loop::run();

echo 'koniec app currency';

