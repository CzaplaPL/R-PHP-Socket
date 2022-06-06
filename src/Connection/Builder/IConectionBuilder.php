<?php

declare(strict_types=1);

namespace App\Connection\builder;

interface IConectionBuilder
{
   public function setAddress(string $addres): self;
   public function setUrl(string $url): self; 
   public function setConnectionType(ConnectionType $type): self;
   public function setPort(string $port): self;
   public function setLoop((LoopInterface $loop): self;
   public function setTimeout(int | bool $timeout): self;
   public function setDns(string | bool $dns): self;
   public function setDnsResolver(ResolverInterface $dnsResolver): self;
   public function setTlsOptions(array $tlsOption): self;
   public function setSocketOptions(array $tlsOption): self;
   public function setHttpHeader(array $tlsOption): self;
}
  
