<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
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
     * @Route("/", name="messaging")
     * @param ConversationRepository $conversationRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ConversationRepository $conversationRepository)
    {
        $currentUser = $this->getUser();

        // this is limited to just 15 if not limit is provided after the user_id
        $conversations = $conversationRepository->findConversationsByUserId($currentUser->getId());

        $finalConversations = [];
        /** @var Conversation $item */
        foreach ($conversations as $item) {
            /** @var Message $lastMessage */
            $lastMessage = $item->getMessages()->last();
            $otherUser = $this->getOtherUser($item, $currentUser);
            $finalConversations[] = [
                'id' => $item->getId(),
                'avatar' => $this->userManager->getUserAvatar($otherUser->getAvatar()),
                'username' => $otherUser->getUsername(),
                'message' => $lastMessage ? $lastMessage->getMessage() : 'No value',
                'date' => $lastMessage ? $this->twig_date->diff($this->environment, $lastMessage->getCreatedAt()) : 'No value'
            ];
        }

        return $this->render('messaging/conversation.html.twig', [
            'conversations' => $finalConversations,
            'conversation_id' => null, // TODO: change this and the line below to something better
            'user' => null
        ]);
    }

    /**
     * @Route("/{id}", name="conversation")
     * @param $id
     * @param ConversationRepository $conversationRepository
     * @return bool|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function conversation($id, ConversationRepository $conversationRepository)
    {
        // the logged in user
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AuthenticationException();
        }
        // TODO: can i improve this ??
        $conversation = null;
        if (!is_null($id)) {
            $conversation = $conversationRepository->find($id);
            if (is_null($conversation)) {
                throw new \Exception('This conversation does\'t exist, this is some shady shit bruh');
            }
        }

        if (!($conversation->getFirstUser() === $currentUser || $conversation->getSecondUser() === $currentUser)) {
            throw new \Exception("You're not allowed to see this conversation!");
        }
        $user = $this->getOtherUser($conversation, $currentUser);
        if (!$user) {
            throw new \Exception('Opps something bad happened!!');
        }

        // this is limited to just 15 if not limit is provided after the user_id
        $conversations = $conversationRepository->findConversationsByUserId($currentUser->getId());

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
     * TODO: remove this after getting everything working
     *
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
