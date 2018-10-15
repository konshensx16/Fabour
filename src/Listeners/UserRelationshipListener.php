<?php
namespace App\Listeners;

use App\Entity\UserRelationship;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserRelationshipListener
{
    public function prePersist(UserRelationship $userRelationship, LifecycleEventArgs $args)
    {
        $userRelationship->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(UserRelationship $userRelationship, LifecycleEventArgs $args)
    {
        $userRelationship->setUpdatedAt(new \DateTime());
    }
}