<?php

namespace App\Form;

use App\Entity\Tag;
use App\Form\DataTransformers\SomethingTransformer;
use App\Form\DataTransformers\StringToArrayTransformer;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;

class TagsType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CollectionToArrayTransformer(), true);
        $builder->addModelTransformer(new StringToArrayTransformer($this->slugify, $this->entityManager), true);
    }

    public function getParent()
    {
        return TextType::class;
    }
}
