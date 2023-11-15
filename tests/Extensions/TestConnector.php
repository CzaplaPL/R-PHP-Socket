<?php

namespace App\Tests\Extensions;

use React\Socket\ConnectorInterface;

class TestConnector implements ConnectorInterface
{

    /**
     * @var TestConnection[]
     */
    private array $connections = [];

    public function __construct(private string $url)
    {
    }

    public function connect($uri)
    {
       if($this->url !== $uri){
           throw new \Exception('Wrong address');
       }

       $connection = new TestConnection();

       $this->connections[] = $connection;

       return $connection;
    }

    public function getUrl(): string
    {
        return $this->url;
    }


    public function setUrl(string $url): void
    {
        $this->url = $url;
    }


}