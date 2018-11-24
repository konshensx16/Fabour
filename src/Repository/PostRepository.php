<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @property  findLatestPosts
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Returns Post[]|null of the most popular posts in a given category
     * based on post views
     * @param int $category_id
     * @param int $limit
     * @return mixed
     */
    public function findPopularPostsByCategoryWithLimit(int $category_id, int $limit)
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.subCategory', 's', 'WITH', 's = p.subCategory')
            ->innerJoin('s.category', 'c', 'WITH', 'c = s.category')
            ->andWhere('c = :category_id')
            ->setParameter('category_id', $category_id)
            ->setMaxResults($limit)
            ->orderBy('p.views_counter', 'DESC')
            ->getQuery();
        return $qb->getResult();
    }

    /**
     * Gets the latest published posts in a category
     * @param int $category_id
     * @return mixed
     */
    public function findRecentPostsWithCategory(int $category_id)
    {
        $qb = $this->createQueryBuilder('p')
        ->innerJoin('p.subCategory', 's', 'WITH', 's = p.subCategory')
        ->innerJoin('s.category', 'c', 'WITH', 'c = s.category')
        ->andWhere('s.category = :category_id')
        ->setParameter('category_id', $category_id)
        ->orderBy('p.created_at', 'DESC')
        ->getQuery();

        // i need to get the category just by using the sub_category
        return $qb->getResult()
        ;
    }

    public function findRecentlyPublishedPostsWithUserIdWithLimit(int $user_id, int $limit)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user_id')
            ->setParameter('user_id', $user_id)
            ->setMaxResults($limit)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Used for a basic search using the name and some wild cards, ofc this is going to change later (be improved)
     * @param string $query
     * @return mixed
     */
    public function findPostsByName(string $query)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.title like :query')
            ->orWhere('p.content like :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLatestPosts()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get the posts with categories and author
     */
    public function findPostsWithCategory()
    {
        // REQUIRED FIELDS: id, title, content, author (user), created_at, sub_category
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('p.id', 'p.title', 'p.content', 'p.created_at', 'u.username', 'sc.name', 'sc.slug')
            ->innerJoin('p.subCategory', 'sc', Join::WITH, 'p.subCategory = sc')
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u');

        return $qb->getQuery()->getResult();
    }
}
