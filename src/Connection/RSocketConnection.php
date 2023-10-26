<?php

declare(strict_types=1);

namespace App\Connection;

use App\Frame\Factory\IFrameFactory;
use App\Frame\RequestResponseFrame;
use React\Socket\ConnectionInterface;
use Rx\Observable;
use Rx\Subject\Subject;

final class RSocketConnection implements IRSocketConnection
{
    // todo VO
    private int $streamId;
    private array $lisseners = [];
    private IFrameFactory $frameFactory;

    public function __construct(private readonly ConnectionInterface $connection, IFrameFactory $frameFactory)
    {
        $this->frameFactory = $frameFactory;
        $this->streamId = 1;
        $this->connection->on('data', function ($data): void {
            $frame = $this->frameFactory->create($data);
            /** @var Subject|null $subject */
            $subject = $this->lisseners[$frame->streamId()];
            if ($subject) {
                if ($frame->next()) {
                    $subject->onNext($frame->payload());
                }
                if ($frame->complete()) {
                    $subject->onCompleted();
                }
            }
        });
    }

    public function getLocalAddress(): ?string
    {
        return $this->connection->getLocalAddress();
    }

    public function conection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function requestResponse(string $data): Observable
    {
        $frame = new RequestResponseFrame($this->streamId, $data);
        $subject = new Subject();
        $this->lisseners[$this->streamId] = $subject;
        $this->connection->write($frame->serialize());
        $this->streamId += 2;

        return $subject->asObservable();
    }

    public function fireAndForget(string $data): void
    {
        $frame = new RequestResponseFrame($this->streamId, $data);
        $this->connection->write($frame->serialize());
    }
}
