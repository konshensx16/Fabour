<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale')
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Profile picture',
                'attr' => [
                    'onchange' => 'previewFile()'
                ]
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('website')
            ->add('phone')
            ->add('twitter')
            ->add('about')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
