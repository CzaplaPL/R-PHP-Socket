<?php

declare(strict_types=1);

namespace App\Connection\Client;

class ConnectionSettings
{
    public function __construct(
        private readonly int $keepAlive = 60000,
        private readonly int $lifetime = 300000,
        private readonly bool $reasumeEnable = false,
        private readonly bool $leaseEnable = false,
        private readonly ?string $reasumeToken = null,
    ) {
    }

    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function isReasumeEnable(): bool
    {
        return $this->reasumeEnable;
    }

    public function isLeaseEnable(): bool
    {
        return $this->leaseEnable;
    }

    public function getReasumeToken(): ?string
    {
        return $this->reasumeToken;
    }

    public function setKeepAlive(int $keepAlive): self
    {
        return new self(
            $keepAlive,
            $this->lifetime,
            $this->reasumeEnable,
            $this->leaseEnable,
            $this->reasumeToken
        );
    }

    public function setLifetime(int $lifetime): self
    {
        return new self(
            $this->keepAlive,
            $lifetime,
            $this->reasumeEnable,
            $this->leaseEnable,
            $this->reasumeToken
        );
    }

    public function setReasumeEnable(bool $reasumeEnable): self
    {
        return new self(
            $this->keepAlive,
            $this->lifetime,
            $reasumeEnable,
            $this->leaseEnable,
            $this->reasumeToken
        );
    }

    public function setLeaseEnable(bool $leaseEnable): self
    {
        return new self(
            $this->keepAlive,
            $this->lifetime,
            $this->reasumeEnable,
            $leaseEnable,
            $this->reasumeToken
        );
    }

    public function setReasumeToken(?string $reasumeToken): self
    {
        return new self(
            $this->keepAlive,
            $this->lifetime,
            $this->reasumeEnable,
            $this->leaseEnable,
            $reasumeToken
        );
    }
}
