<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Services\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @Route("/messages", name="messages.")
 */
class MessagingController extends AbstractController
{

    /**
     * @var \Twig_Extensions_Extension_Date
     */
    private $twig_date;
    /**
     * @var \Twig_Environment
     */
    private $environment;
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(\Twig_Extensions_Extension_Date $twig_date, \Twig_Environment $environment, UserManager $userManager)
    {
        $this->twig_date = $twig_date;
        $this->environment = $environment;
        $this->userManager = $userManager;
    }

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
     * @Route("/{id?}", name="conversation")
     * @param Conversation $conversation
     * @param ConversationRepository $conversationRepository
     * @return bool|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function conversation(Conversation $conversation, ConversationRepository $conversationRepository)
    {
        // logged in user
        $currentUser = $this->getUser();
        // TODO: check if the user is logged in
        if (!$currentUser instanceof User) {
            throw new AuthenticationException();
        }
        // But doing it this way anyone can access other people conversations just by using their ID
        // TODO: check if the current user is a participant in the conversation, if not then thrown an erro and
        // kick him the fuck out of the fucking web app fucking trash ass hacker
        if (!($conversation->getFirstUser() === $currentUser || $conversation->getSecondUser() === $currentUser)) {
            throw new \Exception("You're not allowed to see this conversation!");
        }
        $user = $this->getOtherUser($conversation, $currentUser);
        if (!$user) {
            throw new \Exception('Opps something bad happened!!');
        }

        // TODO: get the conversations where im either the first user or the second!
        $conversations = $conversationRepository->findConversationsByUserId($currentUser->getId());

        // TODO: iterate through all the conversations and make an array with the required values
        $finalConversations = [];
        /** @var Conversation $item */
        foreach ($conversations as $item) {
            $otherUser = $this->getOtherUser($item, $currentUser);
            $finalConversations[] = [
                'id' => $item->getId(),
                'avatar' => $this->userManager->getUserAvatar($otherUser->getAvatar()),
                'username' => $otherUser->getUsername(),
            ];
        }

        dump($finalConversations);

        return $this->render('messaging/conversation.html.twig', [
            'messages' => $conversation->getMessages(),
            'conversations' => $finalConversations,
            'user' => [
                'username' => $user->getUsername(),
                'avatar' => $this->userManager->getUserAvatar($user->getAvatar()),
                'last_seen' => $this->twig_date->diff($this->environment, $user->getLastSeen()),
                'id' => $user->getId(),
                'currentUser' => $this->getUser()->getUsername()
            ],
            'conversation_id' => $conversation->getId()
        ]);
    }

    /**
     * @Route("/tester", name="tester")
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function conversationTester(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        // get ahmed
        $userAhmed = $userRepository->findOneBy(['username' => 'ahmed',
        ]);
        // get admin
        $userAdmin = $userRepository->findOneBy(['username' => 'admin',
        ]);

        $conversation = new Conversation();

        $conversation->setFirstUser($userAdmin);
        $conversation->setSecondUser($userAhmed);

        $entityManager->persist($conversation);
        $entityManager->flush();

        return $this->json(['success' => 'done']);
    }

    /**
     * Given a conversation and a user, this will check if the user is the first or the second
     * NOTE: in this case the user is always the currentUser (this will be removed if the situitation changes)
     * @param Conversation $conversation
     * @param User $user
     * @return User|bool|null
     */
    private function getOtherUser(Conversation $conversation, User $user)
    {
        if ($conversation->getFirstUser() === $user) {
            // TODO: get the second user username and avatar
            return $conversation->getSecondUser();
        } else if ($conversation->getSecondUser() === $user) {
            // TODO: get the first_user username and avatar
            return $conversation->getFirstUser();
        }
        return false;
    }

}
