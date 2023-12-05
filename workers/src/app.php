<?php

use Generator\WorkerGenerator;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Generator/WorkerGenerator.php';

echo 'elo app worker';
echo \App\Frame\Frame::MAJOR_VERSION;
$server = (new \App\Connection\Builder\ConnectionBuilder())->setAddress('172.26.0.2:9091')->createServer();

$server->bind();

\React\EventLoop\Loop::get()->addPeriodicTimer(1, function () use ($server){
    if($server->getConnections()){
        $worker = WorkerGenerator::generate();
        foreach ($server->getConnections() as $connection) {
            $connection->fireAndForget(json_encode($worker));
        }
    }
});
  \React\EventLoop\Loop::run();

  $server->closedConnections()->subscribe(function () {
     \React\EventLoop\Loop::stop();
  });
echo 'koniec app worker';

