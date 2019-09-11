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
        return implode(', ', $array);
    }

    /**
     * @param mixed $string
     * @return array|mixed
     */
    public function reverseTransform($string)
    {
        $names = array_unique(array_filter(array_map('trim', explode(',', $string))));
        $tags = $this->entityManager->getRepository(Tag::class)->findBy([
            'name' => $names
        ]);

        $newNames = array_diff($names, $tags);

        foreach ($newNames as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setSlug($this->slugify->slugify($name));
            $tags[] = $tag;
        }
        return $tags;
    }
}