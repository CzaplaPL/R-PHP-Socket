<?php

namespace App\Tests\Core;

use App\Core\ArrayBuffer;
use App\Core\Enums\ConnectionType;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Core\Url;
use PHPUnit\Framework\TestCase;

final class ArrayBufferTest extends TestCase
{
    /**
     * @dataProvider int24Provider
     * @param Int[] $expected
     */
    public function testAddUInt24(int $number, array $expected): void
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt24($number);

        $this->assertEquals($expected,$buffer->getBuffer());

    }

    /**
     * @dataProvider int32Provider
     * @param Int[] $expected
     */
    public function testAddUInt32(int $number, array $expected): void
    {
        $buffer = new ArrayBuffer();
        $buffer->addUInt32($number);

        $this->assertEquals($expected,$buffer->getBuffer());

    }

    public function int24Provider(): mixed
    {
        return [
            '0'  => [0,[0,0,0]],
            '1'  => [1,[0,0,1]],
            '511'  => [511,[0,1,255]],
            '16777200'  => [16777200,[255,255,240]],
        ];
    }

    public function int32Provider(): mixed
    {
        return [
            '0'  => [0,[0,0,0,0]],
            '1'  => [1,[0,0,0,1]],
            '511'  => [511,[0,0,1,255]],
            '16777200'  => [16777200,[0,255,255,240]],
            '4043309040'  => [4043309040,[240,255,255,240]],
        ];
    }
}


/*

 */