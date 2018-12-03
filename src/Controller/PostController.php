<?php

namespace App\Controller;

use App\Entity\Bookmark;
use App\Entity\Comment;
use App\Entity\Notification;
use App\Entity\NotificationChange;
use App\Entity\NotificationObject;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserRelationship;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\BookmarkRepository;
use App\Repository\UserRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Topic\TopicManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @var TopicManager
     */
    private $topicManager;
    /**
     * @var PusherDecorator
     */
    private $pusher;

    /**
     * @var Packages
     */
    private $packages;

    public function __construct(TopicManager $topicManager, PusherDecorator $pusher, Packages $packages)
    {
        $this->topicManager = $topicManager;
        $this->pusher = $pusher;
        $this->packages = $packages;
    }


    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, UserRelationshipRepository $userRelationshipRepository)
    {
        // i need the form
        $post = new Post();
        $post->setTitle('This is just a draft...');
        $post->setContent('Remove this and start writing here...');
        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post.create')
        ]);

        $em = $this->getDoctrine()->getManager();

        $em->persist($post);
        $em->flush();

        return $this->redirect($this->generateUrl('post.edit', ['id' => $post->getId()]));

        $form->handleRequest($request);
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {

            // TODO: set the publish_at date if the publish button was clicked
            if ($form->get('publish')->isClicked()) {
                $post->setPublishedAt(new \DateTime());
            }

            // handle the request and shit
            $em->persist($post);
            $em->flush();

            // TODO: send a notification to all friends of the publisher
            // get all friends of the user
            $friends = $userRelationshipRepository->findUserFriendsById($currentUser->getId());

            // TODO: this code will have to move somewhere else
            $notificationObject = new NotificationObject();
            $notificationObject->setEntityId($post->getId());
            $notificationObject->setEntityTypeId(
                $this->getEntityTypeId(Post::POST_TYPE_ID)
            );
            $notificationObject->setStatus(1);

            $notificationChange = new NotificationChange();
            $notificationChange->setNotificationObject($notificationObject);
            $notificationChange->setActor($currentUser);
            $notificationChange->setStatus(1);

            $em->persist($notificationObject);
            $em->persist($notificationChange);

            // i only want the related user (just the username)
            // create an array with tht related users usernames
            $friendsNames = [];
            /** @var UserRelationship $friend */
            foreach ($friends as $friend) {
                // create a notification for every friend on the listdi
                $notification = new Notification();
                $notification->setNotificationObject($notificationObject);
                // this is for every single friend in the list
                $notification->setNotifier($friend->getRelatedUser());
                $notification->setStatus(1);

                $em->persist($notification);

                $friendsNames[] = $friend->getRelatedUser()->getUsername();
            }

            $em->flush();
            // send the notification
            try {
                $this->pusher->push([
                    'username' => $currentUser->getUsername(),
                    'action' => 'just published a new post',
                    'notifiers' => $friendsNames,
                    'avatar' => $currentUser->getAvatar(),
                    'url' => $this->generateUrl('post.display', ['id' => $post->getId()]),
                ], 'notification_topic');
            } catch (\Exception $e) {
                $e->getTrace();
            }


            $this->addFlash('success', 'Post published successfully!');

            return $this->redirect($this->generateUrl('post.display', ['id' => $post->getId()]));
        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit")
     * @param Request $request
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Post $post)
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        dump($post);

        if ($form->isSubmitted()) {
            // handle the request and shit
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

        }
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route("/{id}", name="display", options={"expose"=true})
     * @param Request $request
     * @param Post $post
     * @param EntityManagerInterface $em
     * @param BookmarkRepository $bookmarkRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Request $request, Post $post, EntityManagerInterface $em, BookmarkRepository $bookmarkRepository)
    {
        $comment = new Comment();
        // need the comment form
        $commentForm = $this->createForm(CommentType::class, $comment);

        if (!($post->getUser() === $this->getUser())) {
            // TODO: increment the vew counter for this post if not the publisher is viewing it
            $currentViews = $post->getViewsCounter();
            $post->setViewsCounter(++$currentViews);
            $em->persist($post);
            $em->flush(); // NOTE: put this somewhere else maybe?
        }

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted()) {
            // handle the comment and shit
            // TODO: find another (better) way to set the comment to the post!
            //      maybe i need a to set cascade to persist and remove (both Post and Comment for now)
            //      Tbh, there's no way doctrine could figure out what the fuck im talking about in this place
            $comment->setPost($post);
            // TODO: change this to a transaction so in case of error the user will not be notified of the comment by error!
            $em->persist($comment);
            $em->flush();


            // TODO: put this code in an event or a service and trigger the event
            // NOTE: make sure to not make a typo as this wold ruin everything from this point on
            $entity_type_id = $this->getEntityTypeId(Comment::COMMENT_TYPE_ID);
            $notificationObject = new NotificationObject();

            $notificationObject->setEntityTypeId($entity_type_id);
            $notificationObject->setEntityId($comment->getId());
            $notificationObject->setStatus(1); // not sure what this field is for

            $notificationChange = new NotificationChange();
            $notificationChange->setActor($this->getUser());
            $notificationChange->setNotificationObject($notificationObject);
            $notificationChange->setStatus(1);

            $notification = new Notification();
            $notification->setNotificationObject($notificationObject);
            // this is the person who should get the notification, in this case all the friends ?
            $notification->setNotifier($post->getUser());
            $notification->setStatus(1);

            $em->persist($notificationObject);
            $em->persist($notificationChange);
            $em->persist($notification);

            $em->flush();

            // TODO: maybe all this code should be inside an event listener (onCommentPosted!)
            // TODO: if the current logged in user is the author, then no need to send a notification!
            if (!($this->getUser() === $post->getUser())) {
                try {
                    $currentUser = $this->getUser();
                    $this->pusher->push([
                        // this is for the real-time notification, for constructing the notificaion when it arrives
                        // to the front'end
                        'username' => $currentUser->getUsername(),
                        'action' => 'just commented on your post',
                        'notifier' => $post->getUser()->getUsername(),
                        'avatar' => $currentUser->getAvatar(),
                        'url' => $this->generateUrl('post.display', ['id' => $post->getId()]) . '#' . $comment->getId(),
                    ], 'notification_topic');
                } catch (\Exception $e) {
                    $e->getTrace();
                }
            }
            // don't really need to catch anything! i was just testing if it throws any exceptions
            $this->addFlash('success', 'Your comment was posted!');
        }
        return $this->render('post/display.html.twig', [
            'post' => $post,
            'comment_form' => $commentForm->createView(),
            'bookmarked' => $bookmarkRepository->findByUserAndPost(
                $this->getUser()->getId(),
                $post->getId()
            ),
        ]);
    }

    /**
     * @Route("/{id}/bookmark", name="bookmark")
     * @param Request $request
     * @param Post $post
     * @param BookmarkRepository $bookmarkRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bookmark(Request $request, Post $post, BookmarkRepository $bookmarkRepository, EntityManagerInterface $entityManager)
    {
        $currentUser = $this->getUser();

        // TODO: if the user already has then do nothing
        // NOTE: This might throw an error if no results are returned, might want to do heavy tests on this!
        $alreadyExists = $bookmarkRepository->findByUserAndPost(
            $this->getUser()->getId(),
            $post->getId()
        );
        if (!$alreadyExists) {

            $bookmark = new Bookmark();

            $bookmark->setUser($currentUser);
            $bookmark->setPost($post);

            $entityManager->persist($bookmark);
            $entityManager->flush();

            // TODO: this code will have to move somewhere else
            $notificationObject = new NotificationObject();
            $notificationObject->setEntityId($bookmark->getId());
            $notificationObject->setEntityTypeId(
                $this->getEntityTypeId(Bookmark::BOOKMARK_TYPE_ID)
            );
            $notificationObject->setStatus(1);

            $notificationChange = new NotificationChange();
            $notificationChange->setNotificationObject($notificationObject);
            $notificationChange->setActor($currentUser);
            $notificationChange->setStatus(1);

            $entityManager->persist($notificationObject);
            $entityManager->persist($notificationChange);

            $notification = new Notification();
            $notification->setNotificationObject($notificationObject);
            // this is for every single friend in the list
            // notifier is the person to notify
            $notification->setNotifier($post->getUser());
            $notification->setStatus(1);

            $entityManager->persist($notification);
            $entityManager->flush();

            // TODO: maybe all this code should be inside an event listener (onCommentPosted!)
            // TODO: if the current logged in user in the author, then no need to send a notification!
            if (!($this->getUser() === $post->getUser())) {
                try {
                    $currentUser = $this->getUser();
                    $this->pusher->push([
                        // this is for the real-time notification, for constructing the notificaion when it arrive
                        // to the front'end
                        'username' => $currentUser->getUsername(),
                        'action' => 'just bookmarked your post',
                        'notifier' => $post->getUser()->getUsername(),
                        'avatar' => $this->packages->getUrl('assets/img/') . $currentUser->getAvatar(),
                        'url' => $this->generateUrl('post.display', ['id' => $post->getId()]),
                    ], 'notification_topic');
                } catch (\Exception $e) {
                    $e->getTrace();
                }
            }

            $this->addFlash('success', 'Added to your bookmarks');

        }

        return $this->redirect($this->generateUrl('post.display', ['id' => $post->getId()]));
    }

    /**
     * @Route("/{id}/unbookmark", name="unbookmark")
     * @param Post $post
     * @param BookmarkRepository $bookmarkRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unbookmark(Post $post, BookmarkRepository $bookmarkRepository, EntityManagerInterface $entityManager)
    {
        // TODO: remove the bookmark if exists
        $bookmark = $bookmarkRepository->findByUserAndPost($this->getUser()->getId(), $post->getId());

        if ($bookmark) {
            // TODO: maybe use a transaction to be able to catch errors in case of any
            $entityManager->remove($bookmark);
            $entityManager->flush();
        }


        $this->addFlash('success', 'Removed from your bookmarks');

        return $this->redirect($this->generateUrl('post.display', ['id' => $post->getId()]));
    }

    /**
     * @Route("/user/{username}", name="userPosts")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userPosts(User $user)
    {
        return $this->render('post/userPosts.html.twig', [
            'post' => $user->getPosts(),
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
