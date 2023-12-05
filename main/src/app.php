<?php

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\RSocketConnection;
use App\Frame\FireAndForgetFrame;
use function React\Async\await;

require_once __DIR__.'/../vendor/autoload.php';

echo 'elo app main';
echo \App\Frame\Frame::MAJOR_VERSION;

$workers = [];

$client = (new \App\Connection\Builder\ConnectionBuilder())->setAddress('172.26.0.2:9091')->createClient();

/**
 * @var RSocketConnection $connection
 */
$connection = await($client->connect());
$connection->connect(new ConnectionSettings());


$connection->onFnF()->subscribe(function (FireAndForgetFrame $frame )use(&$workers) {
   $workers[] = json_decode($frame->getData(),true);

});
\React\EventLoop\Loop::get()->addPeriodicTimer(5, function () use (&$workers){
    $fp = fopen('pracownicy.csv', 'w');
    foreach ($workers as $worker){
        fputcsv($fp, $worker);
    }
    fclose($fp);
});
echo 'koniec app main';


