<?php

declare(strict_types=1);

namespace App\Connection;

use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Frame\Factory\IFrameFactory;
use App\Frame\Frame;
use App\Frame\RequestResponseFrame;
use App\Frame\SetupFrame;
use Ratchet\Client\WebSocket;
use React\Socket\ConnectionInterface;
use Rx\Observable;
use Rx\Subject\Subject;

abstract class RSocketConnection
{
    public const SETUP_LISSENER_KEY = 'setup';
    public const SETUPED_LISSENER_KEY = 'setuped';
    public const CLOSE_LISSENER_KEY = 'close';
    // todo VO
    protected int $streamId;

    /**
     * @var array<string|int, callable[]>
     */
    protected array $lisseners = [];

    public function __construct(
        protected readonly ConnectionInterface|WebSocket $connection,
        protected readonly IFrameFactory $frameFactory,
        protected bool $connectIsSetuped = false,
    ) {
        $this->streamId = 1;
        $this->connection->on('data', $this->handleData(...));
        $this->connection->on('close', $this->handleClose(...));
    }

    /**
     * @return iterable<Frame>
     */
    abstract protected function decodeFrames(string $data): iterable;

    public function requestResponse(string $data): Observable
    {
        $frame = new RequestResponseFrame($this->streamId, $data);
        $subject = new Subject();
        $this->lisseners[$this->streamId] = $subject;
        $this->send($frame);
        $this->streamId += 2;

        return $subject->asObservable();
    }

    public function fireAndForget(string $data): void
    {
        $frame = new RequestResponseFrame($this->streamId, $data);
        $this->send($frame);
    }

    public function addLissener(string|int $event, callable $setupLissener): void
    {
        if (!isset($this->lisseners[$event])) {
            $this->lisseners[$event] = [];
        }
        $this->lisseners[$event][] = $setupLissener;
    }

    private function setupConnection(SetupFrame $frame): void
    {
        if (!isset($this->lisseners[self::SETUP_LISSENER_KEY])) {
            // sendErrorFrame
            return;
        }

        if ($this->connectIsSetuped) {
            // sendErrorFrame
            return;
        }

        $errorFrame = null;
        foreach ($this->lisseners[self::SETUP_LISSENER_KEY] as $lissener) {
            $errorFrame = $lissener($frame, $this);
        }

        if ($errorFrame) {
            $this->send($errorFrame);

            return;
        }

        $this->connectIsSetuped = true;

        foreach ($this->lisseners[self::SETUPED_LISSENER_KEY] as $lissener) {
            $lissener($this);
        }
    }

    private function handleClose(): void
    {
        $this->connectIsSetuped = false;
        foreach ($this->lisseners[self::CLOSE_LISSENER_KEY] as $lissener) {
            $lissener($this);
        }
    }

    private function handleData(string $data): void
    {
        try {
            foreach ($this->decodeFrames($data) as $frame) {
                if ($frame instanceof SetupFrame) {
                    $this->setupConnection($frame);

                    return;
                }
                //
                //            if($this->connectIsSetuped === false){
                //                //todo
                //            }

                /** @var Subject|null $subject */
                $subject = $this->lisseners[$frame->streamId()] ?? null;
                //                if ($subject) {
                //                    if ($frame->next()) {
                //                        $subject->onNext($frame->payload());
                //                    }
                //                    if ($frame->complete()) {
                //                        $subject->onCompleted();
                //                    }
                //                }
            }
        } catch (CreateFrameOnUnsuportedVersionException) {
            // send error
        }
    }

    private function send(Frame $data): bool
    {
        if ($this->connection instanceof WebSocket) {
            return (bool) $this->connection->send($data);
        }

        return $this->connection->write($data);
    }
}
