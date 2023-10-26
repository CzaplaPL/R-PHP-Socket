<?php

declare(strict_types=1);

namespace App\Frame\Factory;

use App\Frame\IFrame;

interface IFrameFactory
{
    public function create(string $data): IFrame;
}
