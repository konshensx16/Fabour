<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Tutorial;
use App\Services\UuidEncoder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TutorialType extends AbstractType
{
    /**
     * @var UuidEncoder
     */
    private $encoder;

    public function __construct(UuidEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('post', EntityType::class, [
                'class'         => Post::class,
                'mapped'        => false,
                'expanded'      => true,
                'multiple'      => true,
                'choice_attr'   => function (Post $post, $key, $value) {
                    return [
                        'class'             => 'custom_class_' . $post->getTitle(),
                        'data-post-title'   => $post->getTitle()
                    ];
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tutorial::class,
        ]);
    }
}
