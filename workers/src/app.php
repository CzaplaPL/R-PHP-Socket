<?php

use App\Connection\Builder\ConnectionBuilder;
use Generator\WorkerGenerator;
use React\EventLoop\Loop;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Generator/WorkerGenerator.php';

echo 'elo app worker';

$server = (new ConnectionBuilder())->setAddress('172.26.0.2:9091')->createServer();
$server->bind();

\React\EventLoop\Loop::get()->addPeriodicTimer(1, function () use ($server){
    if($server->getConnections()){
        $worker = WorkerGenerator::generate();
        foreach ($server->getConnections() as $connection) {
            $connection->fireAndForget(json_encode($worker));
        }
    }
});

Loop::run();

echo 'koniec app worker';

