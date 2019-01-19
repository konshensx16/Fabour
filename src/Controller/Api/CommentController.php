<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Services\DateManager;
use App\Services\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/api/comment/", name="api_comment.")
 */
class CommentController extends AbstractController
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var CommentRepository
     */
    private $commentRepository;
    /**
     * @var DateManager
     */
    private $dateManager;

    public function __construct(Serializer $serializer, CommentRepository $commentRepository, DateManager $dateManager)
    {
        $this->serializer = $serializer;
        $this->commentRepository = $commentRepository;
        $this->dateManager = $dateManager;
    }
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('api/comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("comments/{uuid}", name="getCommentsForPost", options={"expose"=true})
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCommentsForPost(Post $post)
    {
        $comments = $this->commentRepository->findCommentsForPost($post->getId());
        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['created_at'] = $this->dateManager->timeAgo(($comments[$i]['created_at']));
        }
        // TODO: remove this stuff down here if not needed anymore
//        dump($comments);
//        die;
//        $attributesToIgnore = [
//            'post',
//            'bookmarks',
//            'notificationChanges',
//            'sentMessages',
//            'receivedMessages',
//            'conversations',
//            'friends'
//        ];
//        $serializedData = $this->serializer->serializeToJson($post->getComments(), $attributesToIgnore);
        return $this->json(['comments' => $comments]);
    }
}
