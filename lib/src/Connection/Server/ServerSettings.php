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
        private readonly bool $leaseRequire = false,
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

    public function isLeaseRequire(): bool
    {
        return $this->leaseRequire;
    }

    public function setReasumeEnable(bool $reasumeEnable): self
    {
        return new self(
            $reasumeEnable,
            $this->leaseEnable,
            $this->leaseRequire
        );
    }

    public function setLeaseEnable(bool $leaseEnable): self
    {
        return new self(
            $this->reasumeEnable,
            $leaseEnable,
            $this->leaseRequire
        );
    }

    public function setLeaseRequire(bool $leaseRequire): self
    {
        return new self(
            $this->reasumeEnable,
            $this->leaseEnable,
            $leaseRequire
        );
    }
}
