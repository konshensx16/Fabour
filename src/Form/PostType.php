<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\SubCategory;
use App\Form\DataTransformers\SomethingTransformer;
use App\Form\DataTransformers\StringToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PostType extends AbstractType
{

    /**
     * @var Security
     */
    private $security;
    /**
     * @var StringToArrayTransformer
     */
    private $transformer;
    /**
     * @var SomethingTransformer
     */
    private $somethingTransformer;

    public function __construct(Security $security, StringToArrayTransformer $transformer, SomethingTransformer $somethingTransformer)
    {
        $this->security = $security;
        $this->transformer = $transformer;
        $this->somethingTransformer = $somethingTransformer;
    }

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
            ->add('tags', TagsType::class, [
                'attr' => [
                    'data-role' => "tagsinput"
                ],
                'required' => false
            ]);

        $builder->get('category')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
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
            function (FormEvent $event) {
                $form = $event->getForm(); // entire form
                /** @var Post $data */
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
                } else {
//                    $this->addSubCategoryField($form);
                    $form->add('subCategory', EntityType::class, [
                        'class' => 'App\Entity\SubCategory',
                        'placeholder' => 'Select a subCategory',
                        'required' => false,
                        'choices' => []
                    ]);
                }

                // display buttons according to the published_at entity
                $form->add('save', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-warning btn-block pull-right'
                    ]
                ]);

                if (!$data->getPublishedAt()) {
                    $form
                        ->add('publish', SubmitType::class, [
                            'attr' => [
                                'class' => 'btn btn-primary btn-block'
                            ]
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
