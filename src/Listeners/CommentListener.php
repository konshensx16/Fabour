<?php

namespace App\Listeners;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class CommentListener
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Comment $comment, LifecycleEventArgs $args)
    {
        //TODO: maybe i can set the Post in this place! probably not cause i don't have access to the required Post at this place
        // set the author of the comment to the current logged in user
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            // this should be enough for now
            $comment->setUser($currentUser);
        }
    }
}