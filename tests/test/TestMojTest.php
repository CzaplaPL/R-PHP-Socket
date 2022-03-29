<?php

namespace App\Tests\test;


use App\test\TestMoj;
use PHPUnit\Framework\TestCase;

class TestMojTest extends TestCase
{
    final public function testBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            TestMoj::class,
            new TestMoj(2)
        );
    }

    final public function testAdd(): void
    {
        $obj = new TestMoj(2);
        $obj->setTest2(1);
        $this->assertEquals(2, $obj->getTest());
    }
}
