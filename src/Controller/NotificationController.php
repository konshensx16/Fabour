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
                    $postNotificationObjects = $notificationObjectRepository->findNotificationsByNotifierIdWithPost(
                        $currentUser->getId(),
                        $this->getEntityTypeId('post')
                    );
                    foreach ($postNotificationObjects as $post) {
                        dump($post);
                        $array[] = [
                            'action' => $post['username'] . ' Just published a new post: "'. $post['title'] .'"',
                            'date' => $post['created_at'],
                            'id' => $post['id']
                        ];
                    }
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
