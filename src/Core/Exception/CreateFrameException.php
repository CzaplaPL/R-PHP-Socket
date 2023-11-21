<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class CreateFrameException extends Exception
{
    #[Pure]
    public static function unknowType(int $type): self
    {
        return new self(sprintf('can not create frame to type with number %d', $type));
    }

    #[Pure]
    public static function wrongStreamIdToSetupFrame(int $streamId): self
    {
        return new self(sprintf('%d is wrong stream number to SetupFrame. expected 0', $streamId));
    }

    #[Pure]
    public static function versionNotSuported(int $majorVersion, int $minorVersion): self
    {
        return new self(sprintf('wersion %d.%d not suported', $majorVersion, $minorVersion));
    }
}
