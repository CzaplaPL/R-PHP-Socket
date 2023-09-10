<?php

namespace App\Frame\Factory;

use App\Frame\IFrame;

interface IFrameFactory
{
    Function create(string $data): IFrame;
}