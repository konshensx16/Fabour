<?php

namespace App\Controller\Api;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Services\DateManager;
use App\Services\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/api/comment/", name="api.comment.")
 */
class CommentController extends AbstractController
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;
    /**
     * @var DateManager
     */
    private $dateManager;

    public function __construct(CommentRepository $commentRepository, DateManager $dateManager)
    {
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCommentsForPost(Post $post)
    {
        $comments = $this->commentRepository->findCommentsForPostWithLimitAndOffset($post->getId());
        $totalCommentsCount  = $this->commentRepository->findTotalCommentsCountForPost($post->getId());
        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['created_at'] = $this->dateManager->timeAgo(($comments[$i]['created_at']));
        }
        return $this->json([
            'comments' => $comments,
            'total'    => $totalCommentsCount
        ]);
    }
}
