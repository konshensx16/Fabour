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
        $names = array_unique(array_filter(array_map('trim', explode(',', $string))));
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findBy([
            'name' => $names
        ]);
        $newNames = array_diff($names, $tags);
        $tags = [];
        foreach ($newNames as $k => $v) {
            $v = trim($v);
            $tag = new Tag();
            $tag->setName($v);
            $tag->setSlug($this->slugify->slugify($v));
            $tags[] = $tag;
        }
        return $tags;
    }
}