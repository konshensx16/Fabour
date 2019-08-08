<?php

namespace App\Form\DataTransformers;

use App\Entity\Tag;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SomethingTransformer implements DataTransformerInterface {

    public function transform($array)
    {
//        dump
        $tagNames = [] ;
        /**
         * @var integer $k
         * @var Tag $value
         */
        foreach ($array as $k => $value) {
            $tagNames[] = $value->getName();
        }

        if (is_null($array)) {
            return '';
        }
        dump(implode(', ', $tagNames));
        return implode(', ', $tagNames);
    }

    public function reverseTransform($value)
    {
        dump($value);
    }
}
