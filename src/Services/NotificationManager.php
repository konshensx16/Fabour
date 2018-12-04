<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\NotificationChange;
use App\Entity\NotificationObject;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class NotificationManager
{

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var PusherDecorator
     */
    private $pusher;
    /**
     * @var Router
     */
    private $router;

    public function __construct(PusherDecorator $pusher,
                                ContainerInterface $container,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                RouterInterface $router
    )
    {

        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->pusher = $pusher;
        $this->router = $router;
    }

    public function persistPostNotification()
    {

    }

    /**
     * Gets the entity_type_id from the service container based the given $name
     * Available entity types are
     * Post
     * Comment
     * Bookmark
     * FriendRequest
     * FriendApproval
     * @param string $name
     * @return mixed
     */
    private function getEntityTypeId(string $name)
    {
        return $this->container->getParameter($name . '_type_id');
    }

    /**
     * @param Post $post
     * @param Comment $comment
     * @param User $actor => who is responsible for the notification
     */
    public function persistCommentNotification(Post $post, Comment $comment, User $actor)
    {

        // TODO: put this code in an event or a service and trigger the event
        // NOTE: make sure to not make a typo as this would ruin everything from this point on
        $entity_type_id = $this->getEntityTypeId(Comment::COMMENT_TYPE_ID);
        $notificationObject = new NotificationObject();

        $notificationObject->setEntityTypeId($entity_type_id);
        $notificationObject->setEntityId($comment->getId());
        $notificationObject->setStatus(1); // not sure what this field is for

        $notificationChange = new NotificationChange();
        $notificationChange->setActor($actor);
        $notificationChange->setNotificationObject($notificationObject);
        $notificationChange->setStatus(1);

        $notification = new Notification();
        $notification->setNotificationObject($notificationObject);
        // this is the person who should get the notification, in this case all the friends ?
        $notification->setNotifier($post->getUser());
        $notification->setStatus(1);

        $this->entityManager->persist($notificationObject);
        $this->entityManager->persist($notificationChange);
        $this->entityManager->persist($notification);

        $this->entityManager->flush();
    }

    public function sendNotificationComment(Post $post, Comment $comment)
    {
        // TODO: maybe all this code should be inside an event listener (onCommentPosted!)
        // TODO: if the current logged in user is the author, then no need to send a notification!
        if (!($this->security->getUser() === $post->getUser())) {
            try {
                /** @var User $currentUser */
                $currentUser = $this->security->getUser();
                $this->pusher->push([
                    // this is for the real-time notification, for constructing the notification when it arrives
                    // to the front'end
                    'username' => $currentUser->getUsername(),
                    'action' => 'just commented on your post',
                    'notifier' => $post->getUser()->getUsername(),
                    'avatar' => $currentUser->getAvatar(),
                    'url' => $this->router->generate('post.display', ['id' => $post->getId()]) . '#' . $comment->getId(),
                ], 'notification_topic');
            } catch (\Exception $e) {
                $e->getTrace();
            }
        }
    }


    public function persistBookmarkNotification()
    {

    }

}