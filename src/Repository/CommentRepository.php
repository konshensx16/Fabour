<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }


    /**
     * Get comments for a given post, this should offset and get 10 comments each time
     * @param int $post_id
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function findCommentsForPostWithLimitAndOffset(int $post_id, int $limit = 10, int $offset = 0)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('u.avatar', 'u.username', 'u.id as user_id' , 'c.content', 'c.id as comment_id', 'c.created_at')
            ->innerJoin('c.user', 'u')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.post', $post_id)
                )
            )
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return $qb->getQuery()->getArrayResult();



    }

    /**
     * @param int $post_id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTotalCommentsCountForPost(int $post_id)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select(
                $qb->expr()->count('c.id')
            )
            ->where(
                $qb->expr()->eq('c.post', $post_id)
            )
        ;
        return $qb->getQuery()->getSingleScalarResult();
    }
}
