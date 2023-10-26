<?php

declare(strict_types=1);

namespace App\Frame\Factory;

use _PHPStan_532094bc1\Nette\Neon\Exception;
use App\Core\ArrayBuffer;
use App\Frame\IFrame;
use App\Frame\PayloadFrame;

class FrameFactory implements IFrameFactory
{
    public function create(string $data): IFrame
    {
        $stringArray = str_split($data);
        $buffer = new ArrayBuffer(array_map(static fn ($char) => ord($char), $stringArray));
        $offset = 0;
        $lenght = $buffer->getUInt24($offset);
        $offset += 3;
        $streamId = $buffer->getUInt32($offset);
        $offset += 4;
        $typeAndFlag = $buffer->getUInt16($offset);
        $type = $typeAndFlag >> 10;
        switch ($type) {
            case 10:
                return $this->createPayloadType($buffer, $offset, $streamId, $data);
            default:
                throw new Exception('nie znany typ');
        }
    }

    private function createPayloadType(ArrayBuffer $buffer, int $offset, int $streamId, string $data): PayloadFrame
    {
        $typeAndFlag = $buffer->getUInt16($offset);
        $offset += 2;

        $hasMetaData = ($typeAndFlag & 0x100) === 256;
        $follows = ($typeAndFlag & 0x100) === 128;
        $complete2 = ($typeAndFlag & 0x40);
        $complete = ($typeAndFlag & 0x40) === 64;
        $next = ($typeAndFlag & 0x20) === 32;

        return new PayloadFrame($streamId, substr($data, $offset), $hasMetaData, $follows, $complete, $next);
    }
}
