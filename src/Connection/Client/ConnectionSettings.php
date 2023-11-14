<?php

namespace App\Connection\Client;

class ConnectionSettings
{
    public function __construct(
        private readonly int $keepAlive = 60000,
        private readonly int $lifetime = 300000,
        private readonly bool $reasumeEnable = false,
        private readonly bool $leaseEnable = false,
        private readonly ?string $reasumeToken = null,
    )
    {
    }

    /**
     * @return int
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @return bool
     */
    public function isReasumeEnable(): bool
    {
        return $this->reasumeEnable;
    }

    /**
     * @return bool
     */
    public function isLeaseEnable(): bool
    {
        return $this->leaseEnable;
    }

    /**
     * @return string|null
     */
    public function getReasumeToken(): ?string
    {
        return $this->reasumeToken;
    }

    /**
     * @param int $keepAlive
     */
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

    /**
     * @param int $lifetime
     */
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

    /**
     * @param bool $reasumeEnable
     */
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

    /**
     * @param bool $leaseEnable
     */
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

    /**
     * @param string|null $reasumeToken
     */
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