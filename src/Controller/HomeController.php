<?php

namespace App\Controller;

use App\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        $form = $this->createFormBuilder()
        // id: form_message
            ->add('message', TextType::class)
        // id: form_send
            ->add('send', SubmitType::class)
        // get the form
            ->getForm()
        ;

        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository(Item::class)->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }

    /**
    * @Route("/trigger", name="trigger_event")
    */

    public function triggerEvent()
    {
        
    }
}
