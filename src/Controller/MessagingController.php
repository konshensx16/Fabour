<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\UserRelationshipRepository;
use App\Services\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/messages", name="messages.")
 * @Security("is_granted('ROLE_USER')")
 *
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
    /**
     * @var \App\Services\Serializer
     */
    private $serializer;

    public function __construct(\Twig_Extensions_Extension_Date $twig_date, \Twig_Environment $environment, UserManager $userManager, \App\Services\Serializer $serializer)
    {
        $this->twig_date = $twig_date;
        $this->environment = $environment;
        $this->userManager = $userManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="messaging", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('messaging/conversation.html.twig');
    }

    /**
     * @Route("/con/{id}/{userId}", name="conversation", options={"expose"=true})
     * @param $id
     * @param ConversationRepository $conversationRepository
     * @return bool|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function conversation($id, ConversationRepository $conversationRepository)
    {
        // TODO: this might need to change to something better, haven't decided yet!
        return $this->redirect($this->generateUrl('messages.messaging'));
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

        return $this->render('messaging/conversation.html.twig', [
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
                $finalConversations[$item->getId()] = [
                    'id' => $item->getId(),
                    'avatar' => $otherUser->getAvatar(),
                    'username' => $otherUser->getUsername(),
                    'user_id' => $otherUser->getId(),
                    'message' => $lastMessage ? $lastMessage->getMessage() : 'Conversation is empty',
                    'date' => $lastMessage ? $this->twig_date->diff($this->environment, $lastMessage->getCreatedAt()) : '',
                    'count' => $count,
                    'total' => $item->getMessages()->count(),// TODO: get the total of all messages
                    'offset' => 20,
                    'messages' => []
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
        // get the conversation
        $currentUser = $this->getUser();
        // NOTE: the current user could be the first_user or the second, same thing goes for the other user, so i need to handle both cases
        $conversation = $conversationRepository->findConversationByUsers($currentUser->getId(), $user->getId());

        // check if the current user and that friend are already in a conversation
        // NOTE: there should be either one record or none
        if (is_null($conversation)) {
            // create the new conversation
            $conversation = new Conversation(); // there's nothing wrong with using the same variable since at this point im sure it will be null
            $conversation->setFirstUser($currentUser); // this is the creator of the conversation
            $conversation->setSecondUser($user);

            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        return $this->redirect($this->generateUrl('messages.conversation', ['id' => $conversation->getId(), 'userId' => $user->getId()]));
    }

    /**
     * @Route("/messages/{conversation_id}", name="latestMessages", options={"expose"=true})
     * @param $conversation_id
     * @param Request $request
     * @param ConversationRepository $conversationRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getConversationMessages($conversation_id, Request $request, ConversationRepository $conversationRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $currentUser = $this->getUser();

            // TODO: can i improve this ??
            $conversation = null;
            if (!is_null($conversation_id)) {
                $conversation = $conversationRepository->find($conversation_id);
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

            // mark the messages as read before sending them to the user
            $conversationRepository->updateMessagesReadAt($conversation->getId(), $currentUser->getId());

            $latestMessages = $conversationRepository->findLatestMessagesByConversationIdWithLimit($conversation->getId());
            $messages = [];
            foreach ($latestMessages  as $item) {
                $messages[] = [
                    'username' => $item['senderUsername'],
                    'avatar' => $item['senderAvatar'],
                    'content' => $item['message'],
                    'mine' => $this->isCurrentUserSender($item),
                ];
            }
            return $this->json([
                array_reverse($messages)
            ]);
        }
        throw new \Exception('Something bad happened!');
    }

    /**
     * @Route("/remove/{id}", name="removeConversation", options={"expose"=true})
     * @param Conversation $conversation
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeConversation(Conversation $conversation)
    {
        $currentUser = $this->getUser();
        // TODO: check if the user is able to remove the conversation
        if ($currentUser === $conversation->getFirstUser()) {
            // remove the record
            $em = $this->getDoctrine()->getManager();

            $em->remove($conversation);
            $em->flush();

            return $this->json([
                'success' => true,
            ]);
        }

        return $this->json([
            'failure' => false,
        ]);
    }

    /**
     * Given a conversation and a user, this will check if the user is the first or the second
     * NOTE: in this case the user is always the currentUser (this will be removed if the situation changes)
     * @param Conversation $conversation
     * @param User $user
     * @return User|bool|null
     */
    private function getOtherUser(Conversation $conversation, User $user)
    {
        if ($conversation->getFirstUser() === $user) {
            // get the second user username and avatar
            return $conversation->getSecondUser();
        } else if ($conversation->getSecondUser() === $user) {
            // get the first_user username and avatar
            return $conversation->getFirstUser();
        }
        return false;
    }

    /**
     * Checks if im the owner of the message or not
     * TODO: read the not below to decide
     * NOTE: This is not user anymore? i can just get rid of it i guess
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
        $attributesToIgnore = [
            'post',
            'comments',
            'bookmarks',
            'notificationChanges',
            'sentMessages',
            'receivedMessages',
            'conversations',
            'friends'
        ];

        $friends = $userRelationshipRepository->findFriendsWithLimitByIdJustRelatedUser($currentUser->getId(), $username);
//        dump($friends); die;
        return $this->json([
            'friends' => $this->serializer->serializeToJson($friends, $attributesToIgnore)
        ]);
    }


}
