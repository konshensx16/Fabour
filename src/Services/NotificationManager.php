<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\NotificationChange;
use App\Entity\NotificationObject;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserRelationship;
use App\Repository\UserRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Router;
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
     * @var RouterInterface
     */
    private $router;
    /**
     * @var UserRelationshipRepository
     */
    private $userRelationshipRepository;

    public function __construct(PusherDecorator $pusher,
                                ContainerInterface $container,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                RouterInterface $router,
                                UserRelationshipRepository $userRelationshipRepository
    )
    {

        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->pusher = $pusher;
        $this->router = $router;
        $this->userRelationshipRepository = $userRelationshipRepository;
    }

    public function persistPostNotification(int $user_id, $entity_id, int $entity_type_id, User $actor) // removed the type int from entity_id since using the uuid string value
    {
        // send a notification to all friends of the publisher
        // get all friends of the user
        $friends = $this->userRelationshipRepository->findUserFriendsById($user_id);

        // TODO: this code will have to move somewhere else
        $notificationObject = new NotificationObject();
        $notificationObject->setEntityId($entity_id);
        $notificationObject->setEntityTypeId($entity_type_id);
        $notificationObject->setStatus(1);

        $notificationChange = new NotificationChange();
        $notificationChange->setNotificationObject($notificationObject);
        $notificationChange->setActor($actor); // current user
        $notificationChange->setStatus(1);

        $this->entityManager->persist($notificationObject);
        $this->entityManager->persist($notificationChange);

        // i only want the related user (just the username)
        // create an array with the related users username
        $friendsNames = [];
        /** @var UserRelationship $friend */
        foreach ($friends as $friend) {
            // create a notification for every friend on the list
            $notification = new Notification();
            $notification->setNotificationObject($notificationObject);
            // this is for every single friend in the list
            $notification->setNotifier($friend->getRelatedUser());
            $notification->setStatus(1);

            $this->entityManager->persist($notification);

            $friendsNames[] = $friend->getRelatedUser()->getUsername();
        }

        return $friendsNames;
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

    public function sendNotificationToMultipleUsers(array $data)
    {
        try {
            $this->pusher->push($data, 'notification_topic');
        } catch (\Exception $e) {
            $e->getTrace();
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