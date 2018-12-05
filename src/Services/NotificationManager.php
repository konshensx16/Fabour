<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\NotificationChange;
use App\Entity\NotificationObject;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserRelationship;
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
     * @param int $entity_id
     * @param int $entity_type_id
     * @param User $notifier
     * @param User $actor => who is responsible for the notification
     */
    public function persistCommentNotification(int $entity_id, int $entity_type_id, User $notifier, User $actor)
    {

        // TODO: put this code in an event or a service and trigger the event
        // NOTE: make sure to not make a typo as this would ruin everything from this point on
        $notificationObject = new NotificationObject();

        $notificationObject->setEntityTypeId($entity_type_id);
        $notificationObject->setEntityId($entity_id);
        $notificationObject->setStatus(1);

        $notificationChange = new NotificationChange();
        $notificationChange->setActor($actor);
        $notificationChange->setNotificationObject($notificationObject);
        $notificationChange->setStatus(1);

        $notification = new Notification();
        $notification->setNotificationObject($notificationObject);
        // this is the person who should get the notification, in this case all the friends ?
        $notification->setNotifier($notifier);
        $notification->setStatus(1);

        $this->entityManager->persist($notificationObject);
        $this->entityManager->persist($notificationChange);
        $this->entityManager->persist($notification);

        $this->entityManager->flush();
    }

    /**
     * This will persist the notification to the database, does not work for post currently,
     * to persist a post notification use the dedicated function for that persistPostNotification
     * @param int $entity_id
     * @param int $entity_type_id
     * @param User $notifier
     * @param User $actor
     */
    public function persistBookmarkNotification(int $entity_id, int $entity_type_id, User $notifier, User $actor)
    {
        $notificationObject = new NotificationObject();
        $notificationObject->setEntityId($entity_id);
        $notificationObject->setEntityTypeId($entity_type_id);
        $notificationObject->setStatus(1);

        $notificationChange = new NotificationChange();
        $notificationChange->setNotificationObject($notificationObject);
        $notificationChange->setActor($actor);
        $notificationChange->setStatus(1);

        $this->entityManager->persist($notificationObject);
        $this->entityManager->persist($notificationChange);

        $notification = new Notification();
        $notification->setNotificationObject($notificationObject);
        // this is for every single friend in the list
        // notifier is the person to notify
        $notification->setNotifier($notifier);
        $notification->setStatus(1);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function sendNotification(User $owner, array $data)
    {
        if (!($this->security->getUser() === $owner)) {
            try {
                $this->pusher->push($data, 'notification_topic');
            } catch (\Exception $e) {
                $e->getTrace();
            }
        }
    }

    public function persistFriendshipNotification(User $relatingUser, User $relatedUser, string $type)
    {
        $friendship = new UserRelationship();
        $friendship->setRelatingUser($relatingUser);
        $friendship->setRelatedUser($relatedUser);
        $friendship->setType($type);

        $this->entityManager->persist($friendship);
        $this->entityManager->flush();
    }



}