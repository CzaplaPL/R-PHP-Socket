<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Enums\ConnectionType;
use App\Core\Exception\WrongUrlException;
use JetBrains\PhpStorm\Pure;

final class Url
{
    /**
     * @throws WrongUrlException
     */
    public function __construct(
        private string $url,
        private ConnectionType $connectionType = ConnectionType::TCP,
        private string $port = '80'
    ) {
        if ('' === trim($url)) {
            throw WrongUrlException::wrongAddress();
        }

        if ('' === trim($port)) {
            throw WrongUrlException::wrongPort();
        }
    }

    /**
     * Create Url properties from address
     * examples:
     * 127.90.69.21:80
     * tls://google.com:443
     * ip.com:80.
     *
     * @throws WrongUrlException
     */
    public static function fromAddress(string $address): self
    {
        $urlPosition = strpos($address, '://');
        $connectionType = ConnectionType::TCP;
        if (false !== $urlPosition) {
            $type = substr($address, 0, $urlPosition);
            try {
                $connectionType = ConnectionType::From($type);
            } catch (\ValueError $e) {
                throw WrongUrlException::typeIsNotSupported($type);
            }
            $address = substr($address, $urlPosition + 3);
        }

        $port = '80';
        $portPosition = strpos($address, ':');
        if (false !== $portPosition) {
            $port = substr($address, $portPosition + 1);
            $address = substr($address, 0, $portPosition);
        }

        return new self($address, $connectionType, $port);
    }

    #[Pure]
    /**
     * function return address with connection type and port
     * examples:
     * 127.90.69.21:80
     * tls://google.com:443
     * ip.com:80.
     *
     * @return string address
     */
    public function getAddress(): string
    {
        $url = sprintf('%s:%s', $this->url, $this->port);
        if (ConnectionType::TCP !== $this->connectionType) {
            $url = sprintf('%s://%s', $this->connectionType->value, $url);
        }

        return $url;
    }

    // todo uzupelnic dokumentacje o typy
    /**
     * @param ConnectionType $type [tcp]
     *
     * @throws WrongUrlException
     */
    public function setType(ConnectionType $type): self
    {
        return new self($this->url, $type, $this->port);
    }

    /**
     * @param string $url url address
     *
     * @throws WrongUrlException
     */
    public function setUrl(string $url): self
    {
        return new self($url, $this->connectionType, $this->port);
    }

    /**
     * @param string $port connection port
     *
     * @throws WrongUrlException
     */
    public function setPort(string $port): self
    {
        return new self($this->url, $this->connectionType, $port);
    }

    /**
     * Set Url properties from address
     * examples:
     * 127.90.69.21:80
     * tls://google.com:443
     * ip.com:80.
     *
     * @throws WrongUrlException
     */
    public function setAddress(string $address): self
    {
        return self::fromAddress($address);
    }
}
