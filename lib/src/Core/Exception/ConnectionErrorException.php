<?php

declare(strict_types=1);

namespace App\Core\Exception;

use App\Frame\ErrorFrame;
use Exception;
use Throwable;

class ConnectionErrorException extends Exception
{
    public function __construct(string $message = '', public readonly ?ErrorFrame $errorFrame = null, int $code = 0, Throwable $previus = null)
    {
        parent::__construct($message, $code, $previus);
    }
}
