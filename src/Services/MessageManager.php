<?php

namespace App\Services;

use App\Entity\Message;
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

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     *
     * The sender is current logged in user, that's why it was omitted
     * @param string $message
     * @param string $recipient
     */
    public function saveMessage(string $message, string $recipient)
    {
        // NOTE: the sender is being set in the MessageListener
        // TODO get the recipient object
        $recipientObject = $this->userRepository->findOneBy([
            'username' => $recipient
        ]);


        dump($recipient);

        // TODO: get the current user
        $currentUserUsername = $this->security->getUser()->getUsername();
        $currentUser = $this->userRepository->findOneBy([
            'username' => $currentUserUsername
        ]);

        $messageObject = new Message();

        $messageObject->setMessage($message);
        $messageObject->setReciepent($recipientObject);
        $messageObject->setSender($currentUser);

        $this->entityManager->persist($messageObject);

        $recipientObject->addReceivedMessage($messageObject);
        $currentUser->addSentMessage($messageObject);

        $this->entityManager->flush();
    }

}