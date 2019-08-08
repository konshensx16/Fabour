<?php

namespace App\Form\DataTransformers;

use App\Entity\Tag;
use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class StringToArrayTransformer implements DataTransformerInterface
{

    /**
     * @var Slugify
     */
    private $slugify;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(SlugifyInterface $slugify, EntityManagerInterface $entityManager)
    {
        $this->slugify = $slugify;
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed $array
     * @return mixed|string
     */
    public function transform($array)
    {
        dump($array);
//        $tagNames = [] ;
//        /**
//         * @var integer $k
//         * @var Tag $value
//         */
//        foreach ($array as $k => $value) {
//            $tagNames[] = $value->getName();
//        }
//
//        if (is_null($array)) {
//            return null;
//        }
//        dump(implode(', ', $tagNames));

        return implode(', ', $array);
    }

    /**
     * @param mixed $string
     * @return array|mixed
     */
    public function reverseTransform($string)
    {
        // TODO: maybe check if this is slow or not ?!?!
        $arrayOfValues = explode(',', $string);
        $tags = [];
        // TODO: check if the tag already exists. avoid duplicates
        foreach ($arrayOfValues as $k => $v) {
            $v = trim($v);
            $tag = new Tag();
            $tag->setName($v);
            $tag->setSlug($this->slugify->slugify($v));
            $tags[] = $tag;

            $this->entityManager->persist($tag);
        }

        $this->entityManager->flush();
        dump($tags);
        return $tags;
    }
}