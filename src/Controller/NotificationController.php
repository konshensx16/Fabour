<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationObjectRepository;
use App\Repository\NotificationRepository;
use App\Services\UuidEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/notifications", name="notifications.")
 * @Security("is_granted('ROLE_USER')")
 * Class NotificationController
 * @package App\Controller
 */
class NotificationController extends AbstractController
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var UuidEncoder
     */
    private $uuidEncoder;

    public function __construct(Packages $packages, UuidEncoder $uuidEncoder)
    {
        $this->packages = $packages;
        $this->uuidEncoder = $uuidEncoder;
    }

    /**
     * This is used to render all the notifications in the notification page and not just the dropdown
     * NOTE: Not all of them just the last 100 tbh (maybe should just one week or something)
     * @Route("/all", name="all")
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(NotificationObjectRepository $notificationObjectRepository)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        // this is containing records (NotificationObject) where the current user is the notifier id (array)
        $array = [];

        // TODO: rewrite everything below to take in consideration what's wrote in the dropbox paper
//        $notifications = $notificationObjectRepository->findNotificationsByNotifierId($currentUser->getId());

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
                        $array[] = [
                            'action' => $post['username'] . ' published a new post: "' . $post['title'] . '"',
                            'avatar' => $post['avatar'],
                            'date' => $post['created_at'],
                            'id' => $post['post_id'], // TODO: what am i using this for? because this needs to be encoded
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post['post_id'])]),
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
                            'avatar' => $comment['avatar'],
                            'date' => $comment['created_at'],
                            'id' => $comment['id'],
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($comment['post_id'])]) . '#' . $comment['comment_id'],
                        ];
                    }
                    break;
                case 3:
                    $bookmarkNotificationObjects = $notificationObjectRepository->findNotificationsByNotifierIdWithBookmark(
                        $currentUser->getId(),
                        $this->getEntityTypeId('bookmark')
                    );
                    foreach ($bookmarkNotificationObjects as $post) {
                        $array[] = [
                            'action' => $post['username'] . ' bookmarked your post: "' . $post['title'] . '"',
                            'avatar' => $post['avatar'],
                            'date' => $post['created_at'],
                            'id' => $post['post_id'],
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post['post_id'])]),
                        ];
                    }
                    break;
            }

        }
        // sorting the array based on the id of the sub_array
        usort($array, function ($a, $b) {
            return $b['date'] <=> $a['date']; // SWAP THESE FOR "ASC" OR "DESC"
        });

        return $this->render('notification/index.html.twig', [
            'controller_name' => 'NotificationController',
            'result' => $array,
        ]);
    }

    /**
     * Renders a small list with the current user notifications
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function renderNotifications(NotificationObjectRepository $notificationObjectRepository)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $array = [];
        // TODO: bake the notifications here ? not sure where else to do it
        // TODO: change this to include the count of notifications
        $result = $notificationObjectRepository->findNotificationsDetailsByNotifierIdGroupByEntityId(4);

        foreach ($result as $item) {
            switch ($item['entity_type_id']) {
                case 1:
                    $postNotificationObjects = $notificationObjectRepository->findLatestPostNotifications($item['theCount']);

                    foreach ($postNotificationObjects as $notification) {
                        $array[] = [
                            'id' => $notification['id'],
                            'action' => ' published a new post: "' . $notification['title'] . '"',
                            'avatar' => $notification['avatar'],
                            'date' => $notification['created_at'],
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($notification['post_id'])]),
                            'username' => $notification['username'],
                        ];
                    }

                    break;
                case 2:
                    $notif = $notificationObjectRepository->findLatestComments($item['theCount']);

                    foreach ($notif as $notification) {
//                            dump($notification);
                        $array[] = [
                            'id' => $notification['id'],
                            'action' => ' commented on: ' . $notification['title'],
                            'avatar' => $notification['avatar'],
                            'date' => ($notification['created_at']),
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($notification['post_id'])]),
                            'username' => $notification['username']
                        ];
                    }

                    break;
                case 3:
                    $bookmarkNotificationObjects = $notificationObjectRepository->findLatestBookmarkNotifications($item['theCount']);
                    foreach ($bookmarkNotificationObjects as $notification) {
                        $array[] = [
                            'id' => $notification['id'],
                            'action' => 'bookmarked your post: "' . $notification['title'] . '"',
                            'avatar' => $notification['avatar'],
                            'date' => $notification['created_at'],
                            'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($notification['post_id'])]),
                            'username' => $notification['username'],
                        ];
                    }
                    break;
            }
        }
        // NOTE: this takes less than a second to execute, maybe try millions of values ?
        //      Fill the DB with bunch of records and execute the same on them
        // sorting the array based on the date (maybe i should just include the no.id for easy work)
        usort($array, function ($a, $b) {
            return $b['id'] <=> $a['id'];
        });

        return $this->render('notification/notificationsList.html.twig', [
            'notifications' => $array
        ]);
    }

    /**
     * Mark all user notifications as read (change the status = 1)
     * @Route("/markAllAsRead", name="markAllAsRead")
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function markAllAsRead(Request $request, NotificationObjectRepository $notificationObjectRepository, NotificationRepository $notificationRepository)
    {
        $currentUser = $this->getUser();
        // TODO: get all the notifications and mark them as read
        // NOTE:   use bulk update to avoid wasting time and resources
        //         If necessary use native SQL queries to do this
        //         only get the values that are not read, maybe that will make it a bit faster ?!
        //         is indexing the status going to be any helpful

        $result = $notificationRepository->updateStatusMarkAllAsRead();
        // TODO: this request is meant fo ajax puporses, for now (testing) im keeping it regular redirect.
//        return $this->json([
//            "message" => "You have no unread notifications",
//            "count" => (int)$result,
//        ]);

        return $this->redirect($request->headers->get('referer'));
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

    /**
     * @Route("/grouping")
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return void [type] [description]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function grouping(NotificationObjectRepository $notificationObjectRepository)
    {
        $anotherResult = $notificationObjectRepository->groupCommentsByPosts();

        $result = $notificationObjectRepository->findCountCommentsForPost(13); // 13 => Something is happening!

//            dump($anotherResult);
        die;
    }
}
