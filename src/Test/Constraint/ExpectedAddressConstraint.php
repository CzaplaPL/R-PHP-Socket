<?php

declare(strict_types=1);

namespace App\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

final class ExpectedAddressConstraint extends Constraint
{
    /**
     * @param string[] $connectedAddresses
     */
    public function __construct(private array $connectedAddresses)
    {
    }

    /**
     * @throws ExpectationFailedException
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        if (in_array($other, $this->connectedAddresses, true)) {
            return $returnResult ? true : null;
        }

        if ($returnResult) {
            return false;
        }

        // TODO potrzebny lepszy tekst
        throw new ExpectationFailedException('tekst');
    }

    public function toString(): string
    {
        return 'contains '.$this->exporter()->export($this->connectedAddresses);
    }
}
