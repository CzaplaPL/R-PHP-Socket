<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class ConnectionFailedException extends Exception
{
    #[Pure]
    public static function errorOnSendSetupFrame(\Throwable $error): self
    {
        return new self('error on send setup Frame', $error->getCode(), $error);
    }

    #[Pure]
    public static function errorOnConnecting(string $address, \Throwable $error): self
    {
        return new self(sprintf('error on connecting to %s', $address), $error->getCode(), $error);
    }
}
