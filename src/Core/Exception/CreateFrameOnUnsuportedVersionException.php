<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class CreateFrameOnUnsuportedVersionException extends Exception
{
    #[Pure]
    public static function versionNotSuported(int $majorVersion, int $minorVersion): self
    {
        return new self(sprintf('wersion %d.%d not suported', $majorVersion, $minorVersion));
    }
}
