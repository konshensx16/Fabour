<?php

namespace App\Controller;

use App\Entity\NotificationObject;
use App\Entity\User;
use App\Repository\NotificationObjectRepository;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications", name="notifications.")
 * Class NotificationController
 * @package App\Controller
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/all", name="all")
     * Not all of them just the last 100 tbh (maybe should just one week or something)
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(NotificationObjectRepository $notificationObjectRepository)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        // this is containing records (NotificationObject) where the current user is the notifier id (array)
        $notifications = $notificationObjectRepository->findNotificationsByNotifierId($currentUser->getId());

        $array = [];


        // TODO: bake the notifications here ? not sure where else to do it

        $result = $notificationObjectRepository->findNotificationsDetailsByNotifierId($currentUser->getId());
        foreach ($result as $item) {
            switch ($item['entity_type_id']) {
                case 1:
                    break;
                case 2:
                    // JASMINE COMMENTED ON YOUR POST!
                    $commentNotificationObjects = $notificationObjectRepository->findNotificationsByNotifierIdWithComment(
                        $currentUser->getId(),
                        $this->getEntityTypeId('comment')
                    );
                    foreach ($commentNotificationObjects as $comment) {
                        $array[] = [
                            'action' => $comment['username'] . ' commented on your post.',
                            'date' => $comment['created_at'],
                        ];
                    }
                    break;
            }

        }

        /** @var NotificationObject $notification */
//        foreach ($notifications as $notificationObject) {
//            switch ($notificationObject->getEntityTypeId()) {
//                case 1;
//                    // post
//                    $result = $notificationObjectRepository->findNotificationsByNotifierIdWithPost($currentUser->getId());
//
//                    break;
//                case 2:
//                    // comment
//                    $commentNotificationObjects = $notificationObjectRepository->findNotificationsByNotifierIdWithComment(
//                        $currentUser->getId(),
//                        $this->getEntityTypeId('comment')
//                    );
//                    // this needs to be done outside
//                    foreach ($commentNotificationObjects as $commentNotifObject)
//                    {
//                        dump($commentNotifObject);
//                    }
//                    break;
//                case 3:
//                    // bookmark
//                    $result = $notificationObjectRepository->findNotificationsByNotifierIdWithBookmark($currentUser->getId());
//
//                    break;
//                case 4:
//                    // friendrequest
//                    break;
//                case 5:
//                    // friendqpproval
//                    break;
//            }
//        }

        return $this->render('notification/index.html.twig', [
            'controller_name' => 'NotificationController',
            'result' => $array,
//            'notifications' => $result
        ]);
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
        return $this->getParameter($name . '_type_id');
    }
}
