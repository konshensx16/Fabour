<?php
namespace App\Listeners;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserListener
{

    public function prePersist(User $user, LifecycleEventArgs $args)
    {
        $user->setCreatedAt(new \DateTime());
    }

}