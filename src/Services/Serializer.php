<?php
namespace App\Services;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SFSerializer;

class Serializer
{
    private $encoders;

    public function __construct()
    {
        $this->encoders = [new JsonEncoder()];
    }

    /**
     * Serializes the given data to json format
     * @param $data
     * @param array $ignoredAttributes
     * @return bool|float|int|string
     */
    public function serializeToJson($data, array $ignoredAttributes = [])
    {
        $normalizers = [
            (new ObjectNormalizer())
                ->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                })
                ->setIgnoredAttributes($ignoredAttributes)
        ];

        $serializer = new SFSerializer($normalizers, $this->encoders);

        return $serializer->serialize($data, 'json');
    }
}
