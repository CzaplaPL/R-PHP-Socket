<?php

declare(strict_types=1);

namespace App\Core\Exception;

use JetBrains\PhpStorm\Pure;

class WrongUrlException extends \Exception
{
    #[Pure]
    public static function wrongAddress(): self
    {
        return new self('the given address is incorrect');
    }

    #[Pure]
    public static function typeIsNotSupported(string $type): self
    {
        return new self(sprintf('%s is not supported', $type));
    }

    public static function wrongPort(): self
    {
        return new self('the given port is incorrect');
    }
}
