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
use App\Repository\PostRepository;
use App\Repository\UserRelationshipRepository;
use App\Services\NotificationManager;
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
    /**
     * @var NotificationManager
     */
    private $notificationManager;

    public function __construct(TopicManager $topicManager, PusherDecorator $pusher, Packages $packages, NotificationManager $notificationManager)
    {
        $this->topicManager = $topicManager;
        $this->pusher = $pusher;
        $this->packages = $packages;
        $this->notificationManager = $notificationManager;
    }


    /**
     * @Route("/create", name="create")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create()
    {
        // i need the form
        $post = new Post();
        $post->setTitle('This is just a draft...');
        $post->setContent('Remove this and start writing here...');

        $em = $this->getDoctrine()->getManager();

        $em->persist($post);
        $em->flush();

        return $this->redirect($this->generateUrl('post.edit', ['id' => $post->getId()]));
    }

    /**
     * @Route("/{id}/edit", name="edit")
     * @param Request $request
     * @param Post $post
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Post $post, UserRelationshipRepository $userRelationshipRepository)
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        $currentUser = $this->getUser();

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            // post is being persisted here and inside the if statement
            $em->persist($post);


            // set the publish_at date if the publish button was clicked
            if ($form->get('publish')->isClicked()) {
                $post->setPublishedAt(new \DateTime());
                $em->persist($post);

                // CODE HERE WAS MOVED TO THE FUNCTION
                $friendsNames = $this->notificationManager->persistPostNotification(
                    $currentUser->getId(),
                    $post->getId(),
                    $this->getEntityTypeId(Post::POST_TYPE_ID),
                    $currentUser
                );

                // send the notification
                $notification = [
                    'username' => $currentUser->getUsername(),
                    'action' => 'just published a new post',
                    'notifiers' => $friendsNames,
                    'avatar' => $currentUser->getAvatar(),
                    'url' => $this->generateUrl('post.display', ['id' => $post->getId()]),
                ];

                $this->notificationManager->sendNotificationToMultipleUsers($notification);

                $this->addFlash('success', 'Congratulations, your post was successfully published :)');
            }
            // handle the request and shit
            $em->flush();

        }
        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route("/{id}", name="display", options={"expose"=true}, requirements={"page"="\d+"})
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

            $currentUser = $this->getUser();
            // handle the comment and shit
            // TODO: find another (better) way to set the comment to the post!
            //      maybe i need a to set cascade to persist and remove (both Post and Comment for now)
            //      Tbh, there's no way doctrine could figure out what the fuck im talking about in this place
            $comment->setPost($post);
            // TODO: change this to a transaction so in case of error the user will not be notified of the comment by error!
            $em->persist($comment);
            $em->flush();

            // NOTE: CODE WAS MOVED TO ThE NOTIFICATION MANAGER SERVICE
            //      i feel like this code should return some number or a bool, that i can use to decide
            //      either the send the notification or not (in case of failure)
            $this->notificationManager->persistCommentNotification(
                $comment->getId(),
                $this->getEntityTypeId(Comment::COMMENT_TYPE_ID),
                $comment->getUser(),
                $this->getUser()
            );
            // NOTE: CODE WAS MOVED TO THE FUNCTION
            $notification = [
                'username' => $currentUser->getUsername(),
                'action' => 'just commented on your post',
                'notifier' => $post->getUser()->getUsername(),
                'avatar' => $currentUser->getAvatar(),
                'url' => $this->generateUrl('post.display', ['id' => $post->getId()]) . '#' . $comment->getId(),
            ];
            $this->notificationManager->sendNotification($post->getUser(), $notification);

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
     * @Route("/posts/drafts", name="drafts")
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function drafts(PostRepository $postRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();

        $posts = $postRepository->findDraftsForUser($currentUser->getId());

        return $this->render('post/drafts.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/{id}/bookmark", name="bookmark")
     * @param Post $post
     * @param BookmarkRepository $bookmarkRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bookmark(Post $post, BookmarkRepository $bookmarkRepository, EntityManagerInterface $entityManager)
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

            // CODE HERE WAS MOVED TO THE FUNCTION
            $this->notificationManager->persistBookmarkNotification(
                $bookmark->getId(),
                $this->getEntityTypeId(Bookmark::BOOKMARK_TYPE_ID),
                $post->getUser(),
                $currentUser
            );
            $notification = [
                // this is for the real-time notification, for constructing the notificaion when it arrive
                // to the front'end
                'username' => $currentUser->getUsername(),
                'action' => 'just bookmarked your post',
                'notifier' => $post->getUser()->getUsername(),
                'avatar' => $this->packages->getUrl('assets/img/') . $currentUser->getAvatar(),
                'url' => $this->generateUrl('post.display', ['id' => $post->getId()]),
            ];
            $this->notificationManager->sendNotification($post->getUser(), $notification);

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
