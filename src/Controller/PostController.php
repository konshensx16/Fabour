<?php

namespace App\Controller;

use App\Entity\Bookmark;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\BookmarkRepository;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\SubCategoryRepository;
use App\Services\AttachmentManager;
use App\Services\NotificationManager;
use App\Services\UserManager;
use App\Services\UuidEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Topic\TopicManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Flex\Response;

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
    /**
     * @var AttachmentManager
     */
    private $attachmentManager;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var UuidEncoder
     */
    private $uuidEncoder;

    public function __construct(TopicManager $topicManager,
                                PusherDecorator $pusher,
                                Packages $packages,
                                NotificationManager $notificationManager,
                                AttachmentManager $attachmentManager,
                                UserManager $userManager,
                                UuidEncoder $uuidEncoder
    )
    {
        $this->topicManager = $topicManager;
        $this->pusher = $pusher;
        $this->packages = $packages;
        $this->notificationManager = $notificationManager;
        $this->attachmentManager = $attachmentManager;
        $this->userManager = $userManager;
        $this->uuidEncoder = $uuidEncoder;
    }


    /**
     * @Route("/create", name="create")
     * @Security("is_granted('ROLE_USER')")
     * @param SubCategoryRepository $subCategoryRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(SubCategoryRepository $subCategoryRepository)
    {
        // i need the form
        $post = new Post();
        $post->setTitle('This is just a draft...');
        $post->setContent('Remove this and start writing here...');

        // setting the default category for newly created posts
        // NOTE: doing this because this will not be considered as a draft if it
        //  does not contains a category and subcategory

        // FIXME: this might cause problem if i truncate the BD and re-seed
        $subcategory = $subCategoryRepository->find(1);
        $post->setSubCategory($subcategory);

        $em = $this->getDoctrine()->getManager();

        $em->persist($post);
        $em->flush();

        return $this->redirect($this->generateUrl('post.edit', [
            'uuid' => $this->uuidEncoder->encode($post->getId())
        ]));
    }

    /**
     * @Route("/{uuid}/edit", name="edit")
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @param Request $request
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function edit(Request $request, Post $post)
    {
        $this->checkOwnership($post);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        $currentUser = $this->getUser();

//        dump($post);die;

        if ($form->isSubmitted()) {

            $em = $this->getDoctrine()->getManager();
            dump($post->getTags());
            // post is being persisted here and inside the if statement
            $em->persist($post);
            // set the publish_at date if the publish button was clicked
            if ($form->has('publish') && $form->get('publish')->isClicked()) {
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
                    'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]),
                ];

                $this->notificationManager->sendNotificationToMultipleUsers($notification);

                $this->addFlash('success', 'Congratulations, your post was successfully published :)');
                return $this->redirect($this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]));
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
     * @Route("/{uuid}", name="display", options={"expose"=true})
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
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
            if (!is_null($this->getUser())) {
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
                    $post->getUser(),
                    $this->getUser()
                );
                // NOTE: CODE WAS MOVED TO THE FUNCTION
                $notification = [
                    'username' => $currentUser->getUsername(),
                    'action' => 'just commented on your post',
                    'notifier' => $post->getUser()->getUsername(),
                    'avatar' => $currentUser->getAvatar(),
                    'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]) . '#' . $comment->getId(),
                ];
                $this->notificationManager->sendNotification($post->getUser(), $notification);
                // don't really need to catch anything! i was just testing if it throws any exceptions
                $this->addFlash('success', 'Your comment was posted!');
            }

        }

        // check if user is signed in && check if the user has already bookmarked this
        if (!is_null($this->getUser())) {
            $bookmarked = $bookmarkRepository->findByUserAndPost(
                $this->getUser()->getId(),
                $post->getId()
            );
        }

        return $this->render('post/display.html.twig', [
            'post' => $post,
            'comment_form' => $commentForm->createView(),
            'bookmarked' => $bookmarked ?? null
        ]);
    }

    /**
     * @Route("/delete/{uuid}", name="delete")
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function deletePost(Post $post)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->checkOwnership($post);

        // note: grant if the user is the publisher
        if ($post->getUser() === $this->getUser()) {
            $em = $this->getDoctrine()->getManager();
            // TODO: maybe this should be in the post listener and done after the post has been removed
            // remove all the attachments
            foreach ($post->getAttachments() as $attachment) {
                $this->attachmentManager->removeAttachment($attachment->getFilename());
            }
            $em->remove($post);
            $em->flush();
            return $this->redirect($this->generateUrl('home.index'));
        }

        return new Response('You\'re not allowed to perform this action!');
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
//        dump($posts); die;

        return $this->render('post/drafts.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/posts/publications", name="publications")
     * @param Request $request
     * @param PostRepository $postRepository
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function publications(Request $request, PostRepository $postRepository, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $publications = [];

        if ($currentUser) {
//            $publicationsQuery = $postRepository->findPublicationsByUserId($currentUser->getId());
            $pagination = $postRepository->findPaginatedPostsByUserId(
                $currentUser->getId(),
                $request->query->getInt('page', 1)
            );

        }

        return $this->render('post/publications.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{uuid}/bookmark", name="bookmark")
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @Security("is_granted('ROLE_USER')")
     * @param Post $post
     * @param BookmarkRepository $bookmarkRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function bookmark(Post $post, BookmarkRepository $bookmarkRepository, EntityManagerInterface $entityManager)
    {
        $currentUser = $this->getUser();
        if ($this->getUser() === $post->getUser()) {
            throw new \Exception('This operation cannot be done');
        }
        // NOTE: This might throw an error if no results are returned, might want to do heavy tests on this!
        // if the user already has this bookmark then do nothing
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
                'avatar' => $currentUser->getAvatar(),
                'url' => $this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]),
            ];
            $this->notificationManager->sendNotification($post->getUser(), $notification);

            $this->addFlash('success', 'Added to your bookmarks');

        }

        return $this->redirect($this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]));
    }

    /**
     * @Route("/{uuid}/unbookmark", name="unbookmark")
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @Security("is_granted('ROLE_USER')")
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

        return $this->redirect($this->generateUrl('post.display', ['uuid' => $this->uuidEncoder->encode($post->getId())]));
    }

    /**
     * @Route("/user/{username}", name="userPosts")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userPosts(User $user)
    {
        return $this->render('post/userPosts.html.twig', [
            'posts' => $user->getPosts(),
            'user' => $user,
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

    private function checkOwnership(Post $post)
    {
        if (!$this->userManager->checkPostOwnership($post)) {
            throw new AccessDeniedException("Not enough permission to access this page");
        }
    }


}
