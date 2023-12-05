<?php

declare(strict_types=1);

namespace App\Tests\Extensions\Constraint;

use App\Frame\Frame;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

final class ExpectedSendFrame extends Constraint
{
    /**
     * @param mixed[] $sendedFrame
     */
    public function __construct(private array $sendedFrame)
    {
    }

    /**
     * @throws ExpectationFailedException
     * @param mixed $other
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {

        for ($i = 0; $i < strlen($other); $i++) {
            if($this->sendedFrame[1][$i]!= $other[$i]){
                var_dump($this->sendedFrame[1][$i], $other[$i], $i);
            }
        }



        if (in_array($other, $this->sendedFrame, true)) {
            return $returnResult ? true : null;
        }

        if ($returnResult) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        throw new ExpectationFailedException(sprintf('frame %s not sended', $other));
    }

    public function toString(): string
    {
        return 'contains '.$this->exporter()->export($this->sendedFrame);
    }
}
