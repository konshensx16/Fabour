<?php

namespace App\Controller;

use App\Entity\Bookmark;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Topic\TopicManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    public function __construct(TopicManager $topicManager, PusherDecorator $pusher)
    {
        $this->topicManager = $topicManager;
        $this->pusher = $pusher;
    }


    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, ValidatorInterface $validator)
    {
        // i need the form
        $post = new Post();

        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post.create')
        ]);

        $form->handleRequest($request);
        dump("hello: " . $form->isSubmitted());

        if ($form->isSubmitted() && $form->isValid()) {
            // handle the request and shit
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);

            $errors = $validator->validate($post);
            dump($errors);

            $em->flush();

            $this->addFlash('success', 'Post published successfully!');

            return $this->redirect($this->generateUrl('post.display', ['id' => $post->getId()]));
        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView()
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
     * @Route("/{id}", name="display")
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
            // TODO: maybe all this code should be inside an event listener (onCommentPosted!)
            // TODO: if the current logged in user in the author, then no need to send a notification!
            if (!($this->getUser() === $post->getUser())) {
                try {
                    $this->pusher->push([
                        'message' => $this->getUser()->getUsername() . ' just commented on your post: ' . $post->getTitle(),
                        'author' => $post->getUser()->getUsername()
                    ] , 'comment_topic');
                } catch (\Exception $e)
                {
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
        // TODO: if the user already has then do nothing
        // NOTE: This might throw an error if no results are returned, might want to do heavy tests on this!
        $alreadyExists = $bookmarkRepository->findByUserAndPost(
            $this->getUser()->getId(),
            $post->getId()
        );
        if (!$alreadyExists) {

            $bookmark = new Bookmark();

            $bookmark->setUser($this->getUser());
            $bookmark->setPost($post);

            $entityManager->persist($bookmark);
            $entityManager->flush();

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
            'posts' => $user->getPosts(),
        ]);
    }
}
