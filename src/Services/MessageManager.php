<?php

namespace App\Services;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     *
     * @param string $message
     * @param User $sender
     * @param string $recipient
     */
    public function saveMessage(string $message, User $sender, string $recipient)
    {

        // TODO get the recipient object
        $recipientObject = $this->userRepository->findOneBy([
            'username' => $recipient
        ]);

        $messageObject = new Message();

        $messageObject->setMessage($message);
        $messageObject->setSender($sender);
        $messageObject->setReciepent($recipientObject);

        $this->entityManager->persist($messageObject);
        $this->entityManager->flush();
    }

}