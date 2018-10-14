<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", name="search.")
 * Class SearchController
 * @package App\Controller
 */
class SearchController extends AbstractController
{
    public function searchBar()
    {
        // TODO: create a separate Type for this if needed!
        // This needs an action (and couple of other stuff??)
        $form = $this->createFormBuilder(null)
            ->add('query', TextType::class, [
                'attr' => [
                    'placeholder' => 'Search'
                ]
            ])
            ->getForm();

        // TODO: the form_start and form_end are ruining the design! investigate
        return $this->render('search/searchbar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
