<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;

class ServerErrorException extends Exception
{
    public static function ServerAlreadyBinding(): self
    {
        return new self('Server alerady binding');
    }
}
