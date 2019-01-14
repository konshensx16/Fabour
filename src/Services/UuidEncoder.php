<?php

namespace App\Services;

use Hashids\Hashids;
use Psr\Container\ContainerInterface;

class UuidEncoder
{
    /**
     * @var Hashids
     */
    private $hashids;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->hashids = new Hashids($this->getSalt());
    }
    
    public function encode(int $id): string
    {
        return $this->hashids->encodeHex($id);
    }

    public function decode(string $encoded): ?int
    {
        return $this->hashids->decodeHex($encoded);
    }

    public function getSalt()
    {
        return $this->container->getParameter('hash_salt');
    }
}