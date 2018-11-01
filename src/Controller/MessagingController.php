<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\MessageRepository;

/**
 * @Route("/messages", name="messages.")
 */
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

    /**
     * @Route("/{username}", name="conversation")
     */
    public function conversation(User $user, MessageRepository $messageRepository)
    {
    	if (is_null($user) || empty($user))
    	{
    		// TODO: throw an exception or something
    		return false; // temp solution will change later with everything else
    	}

    	

    	// $messages = 
    	return $this->render('messaging/conversation.html.twig', [

    	]);
    }
}
