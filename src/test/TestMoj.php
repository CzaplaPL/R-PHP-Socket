<?php

declare(strict_types=1);

namespace App\test;

final class TestMoj
{
    private int $test;

    public function __construct(int $test)
    {
        $this->test = $test;
        $this->test2 = 2;
    }

    public function setTest(int $test): void
    {
        $this->test = $test;
    }

    public function setTest2(int $test2): void
    {
        $this->test2 = $test2;
    }

    private int $test2;

    public function getTest(): int
    {
        return $this->test2 > $this->test ? $this->test + $this->test2 : $this->test * $this->test2;
    }
}
