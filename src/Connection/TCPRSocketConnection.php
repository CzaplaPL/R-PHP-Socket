<?php

declare(strict_types=1);

namespace App\Connection;

use App\Core\ArrayBuffer;

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
