<?php

declare(strict_types=1);

namespace App\Connection;

use App\Core\ArrayBuffer;
use App\Frame\Frame;

class TCPRSocketConnection extends RSocketConnection
{
    private int $sizeOfNextFrame = 0;
    private string $previusData = '';

    public function getLocalAddress(): ?string
    {
        return $this->connection->getLocalAddress();
    }

    /**
     * {@inheritdoc}
     */
    protected function decodeFrames(string $data): iterable
    {
        $data = $this->previusData.$data;

        if (0 === $this->sizeOfNextFrame) {
            $this->sizeOfNextFrame = $this->getFrameSize($data);
            $data = substr($data, 3);
        }

        while ($this->sizeOfNextFrame > 0 && strlen($data) >= $this->sizeOfNextFrame) {
            $frameString = substr($data, 0, $this->sizeOfNextFrame);

            yield $this->frameFactory->create($frameString);

            $data = substr($data, $this->sizeOfNextFrame);
            $this->sizeOfNextFrame = $this->getFrameSize($data);
            $data = substr($data, 3);
        }

        $this->previusData = $data;
    }

    public function send(Frame $frame): bool
    {
        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));

        return $this->connection->write($sizeBuffer->toString().$value);
    }

    protected function end(Frame $frame): void
    {
        $value = $frame->serialize();
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($value));
        $this->connection->end($sizeBuffer->toString().$value);
    }

    private function getFrameSize(string $data): int
    {
        if (strlen($data) < 3) {
            return 0;
        }

        $sizeString = substr($data, 0, 3);
        $sizeBuffer = new ArrayBuffer([
           ord($sizeString[0]),
           ord($sizeString[1]),
           ord($sizeString[2]),
        ]);

        return $sizeBuffer->getUInt24(0);
    }
}
