<?php
namespace App\Listeners;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;


class MessageListener
{
    // i need to get the current logged in user
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Message $message, LifecycleEventArgs $args)
    {
        // set the current logged in user to the
        // nor really sure if i want to check if the user is logged in since this page is only
        // accessible to authenticated users
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            // well i think this is fair enough and should work for now!
            // maybe set the time too
            $message->setCreatedAt(new \DateTime());
        }
    }
}