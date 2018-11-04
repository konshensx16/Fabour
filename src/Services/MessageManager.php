<?php

namespace App\Services;

use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class MessageManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                Security $security,
                                ConversationRepository $conversationRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * The sender is current logged in user, that's why it was omitted
     * @param string $message
     * @param string $recipient
     * @param int $conversation_id
     */
    public function saveMessage(string $message, string $recipient, int $conversation_id)
    {
        // TODO: use queues in this case
        // NOTE: the sender is being set in the MessageListener
        // TODO get the recipient object
        $recipientObject = $this->userRepository->findOneBy([
            'username' => $recipient
        ]);
        // TODO: get the conversation object
        $conversation = $this->conversationRepository->find($conversation_id);

        // TODO: get the current user
        $currentUserUsername = $this->security->getUser()->getUsername();
        $currentUser = $this->userRepository->findOneBy([
            'username' => $currentUserUsername
        ]);

        $messageObject = new Message();

        $messageObject->setMessage($message);
        $messageObject->setReciepent($recipientObject);
        $messageObject->setSender($currentUser);

        // TODO: set the conversation_id
        $messageObject->setConversation($conversation);

        $this->entityManager->persist($messageObject);

        $recipientObject->addReceivedMessage($messageObject);
        $currentUser->addSentMessage($messageObject);

        $this->entityManager->flush();
    }

}