<?php

namespace App\Tests\Extensions;

use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class TestConnection implements ConnectionInterface
{
    private ?\Throwable $exceptionOnSendData = null;

    /**
     * @var mixed[]
     */
    private array $sendedData = [];
    /**
     * @var array<mixed,callable[]>
     */
    private array $listeners = [];

    public function getRemoteAddress(): string
    {
       throw new \Exception("do implementacji");
    }

    public function getLocalAddress(): string
    {
        throw new \Exception("do implementacji");
    }

    public function on(mixed $event, callable $listener): ConnectionInterface
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }

    public function once(mixed $event, callable $listener): void
    {
        throw new \Exception("do implementacji");
    }

    public function removeListener(mixed $event, callable $listener): void
    {
        throw new \Exception("do implementacji");
    }

    public function removeAllListeners(mixed $event = null): void
    {
        throw new \Exception("do implementacji");
    }

    public function listeners(mixed $event = null): void
    {
        throw new \Exception("do implementacji");
    }

    /**
     * @param mixed[] $arguments
     */
    public function emit(mixed $event, array $arguments = []): void
    {
        throw new \Exception("do implementacji");
    }

    public function isReadable(): bool
    {
        throw new \Exception("do implementacji");
    }

    public function pause(): void
    {
        throw new \Exception("do implementacji");
    }

    public function resume(): void
    {
        throw new \Exception("do implementacji");
    }

    /**
     * @param mixed[] $options
     */
    public function pipe(WritableStreamInterface $dest, array $options = array()): WritableStreamInterface
    {
        throw new \Exception("do implementacji");
    }

    public function close(): void
    {
        throw new \Exception("do implementacji");
    }

    public function isWritable(): bool
    {
        throw new \Exception("do implementacji");
    }

    public function write(mixed $data): bool
    {
        if($this->exceptionOnSendData) {
            throw $this->exceptionOnSendData;
        }

        $this->sendedData[] = $data;

        return true;
    }

    public function end($data = null): void
    {
        throw new \Exception("do implementacji");
    }

    /**
     * @return mixed[]
     */
    public function getSendedData(): array
    {
        return $this->sendedData;
    }

    public function throwOnSendData(\Throwable|null $exception): void
    {
        $this->exceptionOnSendData = $exception;
    }
}