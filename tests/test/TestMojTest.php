<?php

namespace App\Tests\test;


use App\test\TestMoj;
use PHPUnit\Framework\TestCase;

class TestMojTest extends TestCase
{
    public function testBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            TestMoj::class,
            new TestMoj(2)
        );
    }
}
