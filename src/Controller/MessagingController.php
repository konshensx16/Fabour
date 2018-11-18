<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\UserRelationshipRepository;
use App\Repository\UserRepository;
use App\Services\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('messaging/conversation.html.twig', [
            'conversation_id' => null, // TODO: change this and the line below to something better
            'user' => null
        ]);
    }

    /**
     * @Route("/con/{id}", name="conversation", options={"expose"=true})
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
        // TODO: mark the messages as read before sending them to the user
        $result = $conversationRepository->updateMessagesReadAt($conversation->getId(), $currentUser->getId());
//        dump($result);

        // TODO: get just the latest 20 messages from the conversation
        $latestMessages = $conversationRepository->findLatestMessagesByConversationIdWithLimit($conversation->getId());
//        dump($latestMessages);
        $messages = [];
        foreach ($latestMessages  as $item) {
            dump($item['created_at'] . $item['message']);
            $messages[] = [
                'username' => $item['senderUsername'],
                'avatar' => $item['senderAvatar'],
                'content' => $item['message'],
                'mine' => $this->isCurrentUserSender($item),
            ];
        }

        return $this->render('messaging/conversation.html.twig', [
            'messages' => array_reverse($messages),
            'user' => [
                'username' => $user->getUsername(),
                'avatar' => $user->getAvatar(),
                'last_seen' => $this->twig_date->diff($this->environment, $user->getLastSeen()),
                'id' => $user->getId(),
                'currentUser' => $this->getUser()->getUsername()
            ],
            'conversation_id' => $conversation->getId(),
            'currentUser' => [
                'username' => $currentUser->getUsername(),
                'avatar' => $currentUser->getAvatar(),
            ]
        ]);
    }

    /**
     * @Route("/conversations", name="conversations", options={"expose"=true})
     * @param Request $request
     * @param ConversationRepository $conversationRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getConversations(Request $request, ConversationRepository $conversationRepository)
    {
        // TODO: this should be limited to only  or 20 at a time
        if ($request->isXmlHttpRequest()) {
            $currentUser = $this->getUser();
            // this is limited to just 15 if not limit is provided after the user_id
            $conversations = $conversationRepository->findConversationsByUserId($currentUser->getId());


            $finalConversations = [];
            /** @var Conversation $item */
            foreach ($conversations as $item) {
                /** @var Message $lastMessage */
                $lastMessage = $item->getMessages()->last();
                $count = $conversationRepository->findUnreadMessagesCount($item->getId(), $currentUser->getId());
                $otherUser = $this->getOtherUser($item, $currentUser);
                $finalConversations[] = [
                    'id' => $item->getId(),
                    'avatar' => $otherUser->getAvatar(),
                    'username' => $otherUser->getUsername(),
                    'message' => $lastMessage ? $lastMessage->getMessage() : 'Conversation is empty',
                    'date' => $lastMessage ? $this->twig_date->diff($this->environment, $lastMessage->getCreatedAt()) : '',
                    'count' => $count,
                ];
            }

            return $this->json([
                $finalConversations,
            ]);
        }

        return $this->json([
            'message' => 'Something bad happened',
        ]);
    }


    /**
     * Creates a new conversation
     * @Route("/newConversation/{username}", name="newConversation", options={"expose"=true})
     * @param User $user
     * @param ConversationRepository $conversationRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function newConversation(User $user, ConversationRepository $conversationRepository, EntityManagerInterface $entityManager)
    {
        // TODO: get the conversation
        $currentUser = $this->getUser();
        // NOTE: the current user could be the first_user or the second, same thing goes for the other user, so i need to handle both cases
        $conversation = $conversationRepository->findConversationByUsers($currentUser->getId(), $user->getId());

        // TODO: check if the current user and that friend are already in a conversation
        // NOTE: there should be either one record or none
        if (is_null($conversation)) {
            // TODO: create the new conversation
            $conversation = new Conversation(); // there's nothing wrong with using the same variable since at this point im sure it will be null
            $conversation->setFirstUser($currentUser);
            $conversation->setSecondUser($user);

            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        return $this->redirect($this->generateUrl('messages.conversation', ['id' => $conversation->getId()]));
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

    /**
     * Checks if im the owner of the message or not
     * @param Message $message
     * @return User|null
     */
    private function checkIfCurrentUserIsSenderOrRecipient(Message $message)
    {
        $currentUser = $this->getUser();
        if ($message->getSender() === $currentUser) {
            return $message->getSender();
        }
        return $message->getReciepent();
    }

    /**
     * Check whether the current user is the sender of a given message
     * @param array $message
     * @return bool
     */
    private function isCurrentUserSender(array $message)
    {
//        dump($this->getUser()->getId());
//        dump($message['sender_id']);
//        dump((int)$message['sender_id'] === $this->getUser()->getId()); die;

        return (int)$message['sender_id'] === $this->getUser()->getId();
    }

    /**
     * @Route("/friendsList/{username?}", name="userFriends", options={"expose"=true})
     * @param UserRelationshipRepository $userRelationshipRepository
     * @param $username
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function getUserFriends(UserRelationshipRepository $userRelationshipRepository, $username)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
//
        if (!$currentUser instanceof UserInterface) {
            throw new \Exception('You\'re not logged in!');
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [
            (new ObjectNormalizer())
                ->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                })
                ->setIgnoredAttributes([
                    'posts',
                    'comments',
                    'bookmarks',
                    'notificationChanges',
                    'sentMessages',
                    'receivedMessages',
                    'conversations',
                    'friends'
                ])
        ];

        $serializer = new Serializer($normalizers, $encoders);

        $friends = $userRelationshipRepository->findFriendsWithLimitByIdJustRelatedUser($currentUser->getId(), $username);
        // TODO: fix the avatar of the friends
//        dump($friends); die;
        return $this->json([
            'friends' => $serializer->serialize($friends, 'json')
//            'friends' => $currentUser->getFriends()->toArray()

        ]);
    }


}
