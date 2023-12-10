<?php

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\NewConnection;
use App\Connection\RSocketConnection;
use App\Frame\Frame;
use App\Frame\RequestResponseFrame;
use Generator\BonusesGenerator;
use Generator\ExchangeRateGenerator;
use React\EventLoop\Loop;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Generator/ExchangeRateGenerator.php';

echo 'elo app currency';

$server = (new ConnectionBuilder())->setAddress('172.26.0.3:9091')->createServer();
$server->bind();

$server->newConnections()->subscribe(function (NewConnection $newConnection) {
    $newConnection->connection->onRecivedRequest()->subscribe(function (Frame $frame) use ($newConnection) {
       if($frame instanceof RequestResponseFrame) {
           $exchangeRate = ExchangeRateGenerator::generate();
           $newConnection->connection->sendResponse($frame->streamId(),json_encode($exchangeRate));
       }
   });
});

Loop::run();

echo 'koniec app currency';

