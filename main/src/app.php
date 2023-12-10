<?php

use App\Connection\Builder\ConnectionBuilder;
use App\Connection\Client\ConnectionSettings;
use App\Connection\RSocketConnection;
use App\Core\PayloadDTO;
use App\Frame\FireAndForgetFrame;
use React\EventLoop\Loop;
use function React\Async\await;

require_once __DIR__ . '/../vendor/autoload.php';

echo 'elo app main';

$workers = [];

$workersClient = (new ConnectionBuilder())->setAddress('172.26.0.2:9091')->createClient();
$currencyClient = (new ConnectionBuilder())->setAddress('172.26.0.3:9091')->createClient();
$bonusesClient = (new ConnectionBuilder())->setAddress('172.26.0.4:9091')->createClient();
$provisionClient = (new ConnectionBuilder())->setAddress('172.26.0.5:9091')->createClient();
//
/**
 * @var RSocketConnection $workersConnection
 */
$workersConnection = await($workersClient->connect());
$workersConnection->connect(new ConnectionSettings());

/**
 * @var RSocketConnection $currencyConnection
 */
$currencyConnection = await($currencyClient->connect());
$currencyConnection->connect(new ConnectionSettings());


/**
 * @var RSocketConnection $bonusesConnection
 */
$bonusesConnection = await($bonusesClient->connect());
$bonusesConnection->connect(new ConnectionSettings());

/**
 * @var RSocketConnection $provisionConnection
 */
$provisionConnection = await($provisionClient->connect());
$provisionConnection->connect(new ConnectionSettings());


$request = $provisionConnection->requestChannel(50, '');

$request->response->subscribe(function (PayloadDTO $payload) use (&$workers){
    $provisions = json_decode($payload->data, true);
    foreach ($provisions as $key => $provision) {
        $workers[$key]['provision'] += $provision;
    }
});

$workersConnection->onRecivedRequest()->subscribe(function (FireAndForgetFrame $frame) use (&$workers, $provisionConnection,$request) {
    $worker = json_decode($frame->getData(), true);
    $worker['bonus'] = 0;
    $worker['provision'] = 0;
    $workers[$worker['id']] = $worker;

    if($worker['role'] === 'seller'){
        echo 'wysylam';
        var_dump($worker);
        $provisionConnection->sendResponse($request->streamId, json_encode($worker));
    }
});
$bonusesConnection->requestStream(50, '')->subscribe(function (PayloadDTO $payload) use (&$workers) {
    $bonuses = json_decode($payload->data, true);
    foreach ($bonuses as $key => $bonus) {
        $workers[$key]['bonus'] = $workers[$key]['bonus'] + $bonus;
    }
});


Loop::get()->addPeriodicTimer(10, function () use (&$workers, $currencyConnection) {

    echo 'app';
    $currencyConnection->requestResponse('')->take(1)->subscribe(function (PayloadDTO $payload) use (&$workers) {
        $currencyRate = json_decode($payload->data, true);
        var_dump($currencyRate);
        $currencyRate['PLN'] = 1;
        $fp = fopen('pracownicy.csv', 'w');
        foreach ($workers as $worker) {
            $worker['salary'] = $worker['salary'] * $currencyRate[$worker['currency']];
            fputcsv($fp, $worker);
        }
        fclose($fp);
    });
});

Loop::run();
echo 'koniec app main';


