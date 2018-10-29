<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MessagingController extends AbstractController
{
    /**
     * @Route("/messaging", name="messaging")
     */
    public function index()
    {
        return $this->render('messaging/index.html.twig', [
            'controller_name' => 'MessagingController',
        ]);
    }
}
