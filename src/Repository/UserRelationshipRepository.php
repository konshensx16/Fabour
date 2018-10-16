<?php

namespace App\Repository;

use App\Entity\UserRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
            ->getOneOrNullResult()
        ;
    }

    public function findUsersWithTypeFriend(int $user_id)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.relatingUser = :user_id')
            ->andWhere('u.type = :type')
            ->setParameter('user_id', $user_id)
            ->setParameter('type', 'friend')
            ->getQuery()
            ->getResult()
        ;
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
            ->getResult()
        ;
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
