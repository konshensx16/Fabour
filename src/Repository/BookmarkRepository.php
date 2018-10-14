<?php

namespace App\Repository;

use App\Entity\Bookmark;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Bookmark|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bookmark|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bookmark[]    findAll()
 * @method Bookmark[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookmarkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Bookmark::class);
    }

    /**
     * @param $user_id
     * @param $post_id
     * @return Bookmark Returns a single bookmark that corresponds to the user_id and post_id
     */

    public function findByUserAndPost($user_id, $post_id)
    {
        try {
            return $this->createQueryBuilder('b')
                ->andWhere('b.user = :user_id')
                ->andWhere('b.post = :post_id')
                ->setParameter('user_id', $user_id)
                ->setParameter('post_id', $post_id)
                ->orderBy('b.id', 'ASC')
                ->setMaxResults(10)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }

    /*
    public function findOneBySomeField($value): ?Bookmark
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
