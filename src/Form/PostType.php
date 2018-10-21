<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'required' => false
            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'class' => 'editable tx-16 bd pd-30 tx-inverse'
                ],
                'required' => false,
                'label' => 'Post body'
            ])
            ->add('category', EntityType::class, [
                'class' => 'App\Entity\Category',
                'placeholder' => 'Select a Category',
                'mapped' => false
            ])
        ;

        $builder->get('category')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event)
            {
                $form = $event->getForm();

                $form->getParent()->add('subCategory', EntityType::class, [
                    'class' => 'App\Entity\SubCategory',
                    'placeholder' => 'Select a subCategory',
                    'required' => false,
                    'choices' => $form->getData()->getSubcategories()
                ]);
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event)
            {
                $form = $event->getForm(); // entire form
                $data = $event->getData(); // form data

                /** @var SubCategory $subCategory */
                $subCategory = $data->getSubCategory();

                if ($subCategory) {
                    $form->get('category')->setData($subCategory->getCategory());

                    // TODO: refactor this into a private function (look at the bottom)
//                    $this->addSubCategoryField($form, $subCategory->getCategory()->getSubcategories());
                    $form->add('subCategory', EntityType::class, [
                        'class' => 'App\Entity\SubCategory',
                        'placeholder' => 'Select a subCategory',
                        'required' => false,
                        'choices' => $subCategory->getCategory()->getSubcategories()
                    ]);
                }
                else
                {
//                    $this->addSubCategoryField($form);
                    $form->add('subCategory', EntityType::class, [
                        'class' => 'App\Entity\SubCategory',
                        'placeholder' => 'Select a subCategory',
                        'required' => false,
                        'choices' => []
                    ]);
                }
            }
        );
    }

    private function addSubCategoryField(FormInterface $form, $options = [])
    {
        $form->add('subCategory', EntityType::class, [
            'class' => 'App\Entity\SubCategory',
            'placeholder' => 'Select a SubCategory',
            'choices' => $options
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
