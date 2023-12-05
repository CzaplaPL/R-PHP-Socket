<?php

namespace App\Tests\Extensions;

use App\Frame\Factory\FrameFactory;
use App\Frame\Frame;
use App\Tests\Extensions\Constraint\ExpectedSendFrame;
use PHPUnit\Framework\Constraint\Constraint;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;


class TestConnector implements ConnectorInterface
{
    private ?\Throwable $exceptionOnSendData = null;

    /**
     * @var TestConnection[]
     */
    private array $connections = [];

    /**
     * @var string[]
     */
    private array $expectedSendsData = [];

    public function __construct(private ?string $url = null)
    {
    }

    public function connect($uri): PromiseInterface
    {
       if($this->url && $this->url !== $uri){
           throw new \Exception('Wrong address');
       }

       $connection = new TestConnection();

       if($this->exceptionOnSendData){
           $connection->throwOnSendData($this->exceptionOnSendData);
       }
       $this->connections[] = $connection;

       return new Promise(function (callable $resolver, callable $reject) use ($connection): void {
           $resolver($connection);
       });
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return array<mixed,Constraint>
     */
    public function getConstraints(): array
    {
       $sendedData = [];
       foreach ($this->connections as $connection){
           $sendedData += $connection->getSendedData();
       }

       $constraints = [];
       foreach ($this->expectedSendsData as $expectedSendData) {
           $constraints[$expectedSendData] = new ExpectedSendFrame($sendedData);
       }

       return $constraints;
    }

    public function expectedSendData(string $data): void
    {
        $this->expectedSendsData[] = $data;
    }

    public function throwErrorOnSendData(?\Throwable $exception = new \Exception()): void
    {

        $this->exceptionOnSendData = $exception;

        foreach ($this->connections as $connection){
            $connection->throwOnSendData($exception);
        }
    }

    public function send(string $data)
    {
        foreach ($this->connections as $connection) {
            $connection->emit('data',[ $data]);
        }
    }


}