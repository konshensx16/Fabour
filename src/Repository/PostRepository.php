<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Services\UuidEncoder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @property  findLatestPosts
 */
class PostRepository extends ServiceEntityRepository
{
    use FindByEncodedIdTrait;

    public function __construct(RegistryInterface $registry, UuidEncoder $uuidEncoder)
    {
        parent::__construct($registry, Post::class);
        $this->uuidEncoder = $uuidEncoder;
    }

    /**
     * Returns Post[]|null of the most popular post in a given category
     * based on post views
     * @param int $category_id
     * @param int $limit
     * @return mixed
     */
    public function findPopularPostsByCategoryWithLimit(int $category_id, int $limit)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->innerJoin('p.subCategory', 's', 'WITH', 's = p.subCategory')
            ->innerJoin('s.category', 'c', 'WITH', 'c = s.category')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.category', ':category_id'),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('category_id', $category_id)
            ->setMaxResults($limit)
            ->orderBy('p.views_counter', 'DESC')
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * Gets the latest published post in a category
     * @param int $category_id
     * @return mixed
     */
    public function findRecentPostsWithCategory(int $category_id)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
        ->innerJoin('p.subCategory', 's', 'WITH', 's = p.subCategory')
        ->innerJoin('s.category', 'c', 'WITH', 'c = s.category')
        ->where(
            $qb->expr()->andX(
                $qb->expr()->eq('s.category', ':category_id'),
                $qb->expr()->isNotNull('p.published_at')
            )
        )
//        ->andWhere('s.category = :category_id')
        ->setParameter('category_id', $category_id)
        ->orderBy('p.created_at', 'DESC')

        ;

        // i need to get the category just by using the sub_category
        return $qb->getQuery()->getResult()
        ;
    }

    public function findRecentlyPublishedPostsWithUserIdWithLimit(int $user_id, int $limit)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('p.user', ':user_id'),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('user_id', $user_id)
            ->setMaxResults($limit)
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * I might need to change this to support offset and use it for pagination later
     * @param string $username
     * @param int $limit
     * @param int $offset
     * @return Post[] || null
     */
    public function findRecentlyPublishedPostsByUsernameWithLimit(string $username, int $limit = 10, int $offset = 0)
    {
        $qb = $this->createQueryBuilder('p');
        // p.id, p.title, p.content, u.username, subcategory.name
        return $qb
            ->select('p.id', 'p.title', 'p.content', 'u.username', 'sc.name', 'sc.slug', 'p.created_at')
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u')
            ->innerJoin('p.subCategory', 'sc', Join::WITH, 'p.subCategory = sc')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.username', ':username'),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('username', $username)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('p.published_at', 'DESC')
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
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.title', ':query'),
                        $qb->expr()->like('p.content', ':query')

                    ),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('query', '%' . $query . '%')
        ;
        return $qb
            ->getQuery()
            ->getResult();
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
     * Get the post with categories and author
     */
    public function findPostsWithCategory()
    {
        // REQUIRED FIELDS: id, title, content, author (user), created_at, sub_category
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('p.id', 'p.title', 'p.content', 'p.created_at', 'u.username', 'sc.name', 'sc.slug')
            ->innerJoin('p.subCategory', 'sc', Join::WITH, 'p.subCategory = sc')
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u')
            ->where(
                $qb->expr()->isNotNull('p.published_at')
            )
        ;

        dump($qb->getQuery()->getSQL());

        return $qb->getQuery()->getResult();
    }

    public function findRecentlyPublishedPostsByWithLimit(int $limit = 10, int $offset = 0)
    {
        $qb = $this->createQueryBuilder('p');
        // p.id, p.title, p.content, u.username, subcategory.name
        return $qb
            ->select('p.id', 'p.title', 'p.content', 'u.username', 'sc.name', 'sc.slug', 'p.published_at AS created_at')
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u')
            ->innerJoin('p.subCategory', 'sc', Join::WITH, 'p.subCategory = sc')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('p.published_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the total posts count
     * NOTE: this only needs to return the count of the post that are published and not include drafts
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalPosts()
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select($qb->expr()->count('p.id'))
            ->where(
                $qb->expr()->isNotNull('p.published_at')
            )
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns the total posts count for a given user
     * @param string $username
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalPostsByUsername(string $username)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select($qb->expr()->count('p.id'))
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.username', ':username'),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('username', $username)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findDraftsForUser(int $user_id, int $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.id', 'p.title', 'p.content', 'u.username', 'sc.name', 'sc.slug', 'p.created_at')
            ->innerJoin('p.user', 'u', Join::WITH, 'p.user = u')
            ->innerJoin('p.subCategory', 'sc', Join::WITH, 'p.subCategory = sc')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.id', ':user_id'),
                    $qb->expr()->isNull('p.published_at')
                )
            )
            ->setParameter('user_id', $user_id)
            ->setMaxResults($limit)
            ->orderBy('p.created_at', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findRecentPostsWithSubCategory(int $subcategory_id)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
//            ->innerJoin('p.subCategory', 's', 'WITH', 's = p.subCategory')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('p.subCategory', ':subcategory_id'),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
            ->setParameter('subcategory_id', $subcategory_id)
            ->orderBy('p.created_at', 'DESC')
        ;

        // i need to get the category just by using the sub_category
        return $qb->getQuery()->getResult();
    }


    public function getCountPosts()
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select(
            $qb->expr()->count('p.id')
        )
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getPosts(?int $offset = 0, int $limit = 1)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getArrayResult();
    }

    public function findPublicationsByUserId(int $userId)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select()
            ->innerJoin('p.user', 'u')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.id', $userId),
                    $qb->expr()->isNotNull('p.published_at')
                )
            )
        ;



        return $qb;
    }


}
