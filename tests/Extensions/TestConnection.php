<?php

namespace App\Tests\Extensions;

use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class TestConnection implements ConnectionInterface
{

    public function getRemoteAddress()
    {
       throw new \Exception("do implementacji");
    }

    public function getLocalAddress()
    {
        throw new \Exception("do implementacji");
    }

    public function on($event, callable $listener)
    {
        throw new \Exception("do implementacji");
    }

    public function once($event, callable $listener)
    {
        throw new \Exception("do implementacji");
    }

    public function removeListener($event, callable $listener)
    {
        throw new \Exception("do implementacji");
    }

    public function removeAllListeners($event = null)
    {
        throw new \Exception("do implementacji");
    }

    public function listeners($event = null)
    {
        throw new \Exception("do implementacji");
    }

    public function emit($event, array $arguments = [])
    {
        throw new \Exception("do implementacji");
    }

    public function isReadable()
    {
        throw new \Exception("do implementacji");
    }

    public function pause()
    {
        throw new \Exception("do implementacji");
    }

    public function resume()
    {
        throw new \Exception("do implementacji");
    }

    public function pipe(WritableStreamInterface $dest, array $options = array())
    {
        throw new \Exception("do implementacji");
    }

    public function close()
    {
        throw new \Exception("do implementacji");
    }

    public function isWritable()
    {
        throw new \Exception("do implementacji");
    }

    public function write($data)
    {
        throw new \Exception("do implementacji");
    }

    public function end($data = null)
    {
        throw new \Exception("do implementacji");
    }
}