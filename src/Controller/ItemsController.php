<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/items", name="items.")
 */
class ItemsController extends AbstractController
{
    /**
     * @Route("/index", name="index")
     */
    public function index()
    {
        
        return $this->render('items/index.html.twig', [
            'controller_name' => 'ItemsController',
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {

        // create a form type
        $item = new Item();
        $form = $this->createForm(ItemFormType::class, $item);

        // handle request
        $form->handleRequest($request);
        // handle form submission
        if ($form->isSubmitted())
        {

        }

        return $this->render('items/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update", name="update")
     */
    public function update()
    {
        return $this->render('items/index.html.twig', [
            'controller_name' => 'ItemsController',
        ]);
    }

    /**
     * @Route("/remove", name="remove")
     */
    public function remove()
    {
        return $this->render('items/index.html.twig', [
            'controller_name' => 'ItemsController',
        ]);
    }
}
