<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class WrongConfigurationException extends Exception
{
    #[Pure]
    public static function wrongLifetime(): self
    {
        return new self('lifetime setings must be bigger than 0');
    }

    #[Pure]
    public static function wrongKeepAlive(): self
    {
        return new self('keepAlive setings must be bigger than 0');
    }
}
