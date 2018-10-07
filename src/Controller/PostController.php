<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Topic\TopicManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    public function __construct(TopicManager $topicManager, PusherDecorator $pusher)
    {
        $this->topicManager = $topicManager;
        $this->pusher = $pusher;
    }
    

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        // i need the form
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // handle the request and shit
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post published successfully!');

            return $this->redirect($this->generateUrl('display', ['id' => $post->getId()]));

        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit")
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function display(Request $request, Post $post)
    {
        $comment = new Comment();
        // need the comment form
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted()) {
            // handle the comment and shit
            $em = $this->getDoctrine()->getManager();
            // TODO: find another (better) way to set the comment to the post!
            $comment->setPost($post);
            // TODO: change this to a transaction so in case of error the user will not be notified of the comment by error!
//            $em->persist($comment);
//            $em->flush();
            /*
            $topic = $this->topicManager->getTopic('acme/channel');
            // this is going to broadcast to everyone i think ??
            $topic->broadcast('Hello from the controller!');
            */
            // don't really need to catch anything! i was just testing if it throws any exceptions
            try {
                $this->pusher->push(['msg' => 'Hello from the controller'] , 'acme_topic');
            } catch (\Exception $e)
            {
                $e->getTrace();
            }
            $this->addFlash('success', 'Your comment was posted!');
        }
        return $this->render('post/display.html.twig', [
            'post' => $post,
            'comment_form' => $commentForm->createView()
        ]);
    }
}
