<?php

namespace App\Repository;

use App\Services\UuidEncoder;

trait FindByEncodedIdTrait
{
    /**
     * @var UuidEncoder
     */
    protected $uuidEncoder;

    public function findOneByEncodedId($uuid)
    {
        return $this->find(
            $this->uuidEncoder->decode($uuid)
        );
    }
}