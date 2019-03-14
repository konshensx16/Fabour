<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Services\DateManager;
use App\Services\Serializer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/api/comment", name="api.comment.")
 */
class CommentController extends AbstractController
{
    const COMMENTS_LIMIT = 10;

    /**
     * @var CommentRepository
     */
    private $commentRepository;
    /**
     * @var DateManager
     */
    private $dateManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(CommentRepository $commentRepository, DateManager $dateManager, EntityManagerInterface $entityManager)
    {
        $this->commentRepository = $commentRepository;
        $this->dateManager = $dateManager;
        $this->entityManager = $entityManager;
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
     * @Route("/comments/{uuid}", name="getCommentsForPost", options={"expose"=true})
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

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"}, options={"expose"=true})
     * @param Comment $comment
     * @return bool|Response
     */
    public function deleteComment(Comment $comment)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser->getId() === $comment->getUser()->getId())
            return false;
        // remove the comment
        if ($comment) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }
        return false;
    }

    /**
     * @Route("/{id}/update", name="update", methods={"DELETE"}, options={"expose"=true})
     * @param Comment $comment
     * @return bool
     */
    public function updateComment(Comment $comment)
    {
        dump($comment);die;
        // remove the comment
        if ($comment) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();

            return true;
        }
        return false;
    }

    /**
     * @Route("/moreComments/{uuid}/{offset}", name="getMoreCommentsForPost", options={"expose"=true})
     * @Entity("post", expr="repository.findOneByEncodedId(uuid)")
     * @param Post $post
     * @param int $offset
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMoreCommentsForPost(Post $post, int $offset)
    {
        $comments = $this->commentRepository->findCommentsForPostWithLimitAndOffset($post->getId(), self::COMMENTS_LIMIT, $offset);
        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['created_at'] = $this->dateManager->timeAgo(($comments[$i]['created_at']));
        }
        return $this->json($comments);
    }

}
