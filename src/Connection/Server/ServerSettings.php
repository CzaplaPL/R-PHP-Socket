<?php

declare(strict_types=1);

namespace App\Connection\Server;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class ServerSettings
{
    public function __construct(
        private readonly bool $reasumeEnable = false,
        private readonly bool $leaseEnable = false,
    ) {
    }

    public function isReasumeEnable(): bool
    {
        return $this->reasumeEnable;
    }

    public function isLeaseEnable(): bool
    {
        return $this->leaseEnable;
    }

    public function setReasumeEnable(bool $reasumeEnable): self
    {
        return new self(
            $reasumeEnable,
            $this->leaseEnable,
        );
    }

    public function setLeaseEnable(bool $leaseEnable): self
    {
        return new self(
            $this->reasumeEnable,
            $leaseEnable,
        );
    }
}
