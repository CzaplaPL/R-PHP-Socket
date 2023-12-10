<?php

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Frame\FireAndForgetFrame;
use App\Frame\Frame;
use App\Frame\RequestResponseFrame;
use App\Frame\RequestStreamFrame;
use Generator\BonusesGenerator;
use React\EventLoop\Loop;
use function React\Async\await;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Generator/BonusesGenerator.php';

echo 'elo app bonuses';

$workers = [];

$workersClient = (new ConnectionBuilder())->setAddress('172.26.0.2:9091')->createClient();

/**
 * @var RSocketConnection $workersConnection
 */
$workersConnection = await($workersClient->connect());
$workersConnection->connect(new ConnectionSettings());


$workersConnection->onRecivedRequest()->subscribe(function (FireAndForgetFrame $frame) use (&$workers) {
    $workers[] = json_decode($frame->getData(), true);

});

$server = (new ConnectionBuilder())->setAddress('172.26.0.4:9091')->createServer();
$server->bind();

$server->newConnections()->subscribe(function (NewConnection $newConnection) use(&$workers) {
    $newConnection->connection->onRecivedRequest()->subscribe(function (Frame $frame) use ($newConnection, &$workers) {
        if($frame instanceof RequestStreamFrame) {
            Loop::get()->addPeriodicTimer(4, function () use (&$workers, $newConnection, $frame) {
                $response = BonusesGenerator::generate($workers);
                $newConnection->connection->sendResponse($frame->streamId(),json_encode($response));
            });
        }
    });
});



Loop::run();

echo 'koniec app currency';

