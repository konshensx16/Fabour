<?php

namespace App\Repository;

use App\Entity\UserRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserRelationship|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRelationship|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRelationship[]    findAll()
 * @method UserRelationship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRelationshipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserRelationship::class);
    }

    /**
     * @param $relating_user_id
     * @param $related_user_id
     * @return UserRelationship|null Returns an array of UserRelationship objects
     * @throws NonUniqueResultException
     */
    public function findByRelatingUserAndRelatedUser(int $relating_user_id, int $related_user_id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatingUser = :relatingUser')
            ->andWhere('u.relatedUser = :relatedUser')
            ->setParameter('relatingUser', $relating_user_id)
            ->setParameter('relatedUser', $related_user_id)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUsersWithTypeFriend(int $user_id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatingUser = :user_id')
            ->andWhere('u.type = :type')
            ->setParameter('user_id', $user_id)
            ->setParameter('type', 'friend')
            ->getQuery()
            ->getResult();
    }

    /**
     * Using the relatedUser_id because we want the record where the current logged in is mentioned
     * which means will return where people wants to be friends with me, since im the related user
     * TODO: check this heavily with multiple user to make sure it works and doesn't break under certain conditions
     * @param int $user_id
     * @return mixed
     */
    public function findUsersWithTypePending(int $user_id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatedUser = :user_id')
            ->andWhere('u.type = :type')
            ->setParameter('user_id', $user_id)
            ->setParameter('type', 'pending')
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets a User|null based on type 'friend' and relatedUserId
     * @param $relating_user_id
     * @param $related_user_id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneFriendById(int $relating_user_id, int $related_user_id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatingUser = :relatingUser')
            ->andWhere('u.relatedUser = :relatedUser')
            ->andWhere('u.type = :type')
            ->setParameter('relatingUser', $relating_user_id)
            ->setParameter('relatedUser', $related_user_id)
            ->setParameter('type', 'friend')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Will get the some friends (limit) of some given user (using id)
     * If limit is not set it's going to return just 1000
     * Returns UserRelationship[]|null
     * @param int $user_id
     * @param int $limit
     * @return mixed
     */
    public function findFriendsWithLimitById(int $user_id, int $limit = 1000)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatingUser = :user_id')
            ->andWhere('u.type = :type')
            ->setParameter('user_id', $user_id)
            ->setParameter('type', 'friend')
            ->orderBy('u.updated_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }

    /**
     * Will get friends (limit) of some given user (using id)
     * If limit is not set it's going to return just 1000
     * Returns UserRelationship[]|null
     * @param int $user_id
     * @param string $username
     * @param int $limit
     * @return mixed
     */
    public function findFriendsWithLimitByIdJustRelatedUser(int $user_id, string $username, int $limit = 1000)
    {
        return $this->createQueryBuilder('u')
            ->select('ru.username', 'ru.avatar', 'ru.avatar')
            ->innerJoin('u.relatedUser', 'ru')
            ->andWhere('u.relatingUser = :user_id')
            ->andWhere('ru.username like :username')
            ->andWhere('u.type = :type')
            ->setParameter('user_id', $user_id)
            ->setParameter('username', '%' . $username . '%')
            ->setParameter('type', 'friend')
            ->orderBy('u.updated_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }

    /**
     * Gets a list of users related to the $user_id
     * @param int $user_id
     * @return mixed
     */
    public function findUserFriendsById(int $user_id)
    {
        return $this->createQueryBuilder('ur')
//            ->select('ur.relatedUser')
            ->andWhere('ur.relatingUser = :user_id')
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->getResult();

    }


    public function findFriendsByUsername(int $user_id, string $username)
    {
        $qb = $this->createQueryBuilder('ur')
            ->innerJoin('ur.relatedUser', 'u', Join::WITH, 'ur.relatedUser = u.id')
            ->andWhere('ur.relatingUser = :user_id')
            ->andWhere('u.username like :username')
            ->setParameter('user_id', $user_id)
            ->setParameter('username', '%' . $username . '%');

        return $qb
            ->getQuery()
            ->getResult();
    }




//    public function findOneBySomeField($value): ?UserRelationship
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
