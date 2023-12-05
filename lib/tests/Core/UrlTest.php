<?php

namespace App\Tests\Core;

use App\Core\Enums\ConnectionType;
use App\Core\Exception\WrongUrlException;
use App\Core\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    /**
     * @dataProvider correctAddressProvider
     */
    public function testCreateUrlFromCorrectAddress(string $address, string $expected): void
    {
        $url =  Url::fromAddress($address);
        $this->assertEquals($expected, $url->getAddress());

    }

    /**
     * @dataProvider wrongAddressProvider
     */
    public function testCreateUrlFromWrongAddress(string $address): void
    {
        $this->expectException(WrongUrlException::class);
        $url =  Url::fromAddress($address);
    }

    public function testCreateUrlFromNotSupportedType(): void
    {
        $this->expectException(WrongUrlException::class);
        $url =  Url::fromAddress('new://ip:80');
    }

    public function testSetWrongPort(): void
    {
        $url = new Url('ip');
        $this->expectException(WrongUrlException::class);
        $url->setPort(' ');
    }

    public function testSetAddress(): void
    {
        $url = new Url('ip');
        $url = $url->setAddress('127.90.69.21:443');
        $this->assertEquals('127.90.69.21:443', $url->getAddress());
    }

    public function testSetUrl(): void
    {
        $url = new Url('ip');
        $url = $url->setUrl('127.90.69.21');
        $this->assertEquals('127.90.69.21:80', $url->getAddress());
    }

    public function testSetConnectionType(): void
    {
        $url = new Url('ip');
        $url = $url->setType(ConnectionType::TLS);
        $this->assertEquals('tls://ip:80', $url->getAddress());
    }

    public function correctAddressProvider(): mixed
    {
        return [
            '127.90.69.21:443'  => ['127.90.69.21:443','127.90.69.21:443'],
            'tls://google.com:443' => ['tls://google.com:443','tls://google.com:443'],
            'tcp://ip.com' => ['tcp://ip.com','ip.com:80'],
            '125.0.0.1'  => ['125.0.0.1:80','125.0.0.1:80']
        ];
    }

    public function wrongAddressProvider(): mixed
    {
        return [
            'tcp://' => ['tcp://'],
            ':90' => [':90'],
            ' ' => [' '],
        ];
    }
}


/*

 */