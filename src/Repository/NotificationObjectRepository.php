<?php

namespace App\Repository;

use App\Entity\NotificationObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NotificationObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationObject[]    findAll()
 * @method NotificationObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationObjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NotificationObject::class);
    }

    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierId(int $notifier_id)
    {
        $qb = $this->createQueryBuilder('no')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $notifier_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults(100);
        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @param int $entity_type_id
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierIdWithPost(int $notifier_id, int $entity_type_id)
    {
        $qb = $this->createQueryBuilder('no')
            ->select('actor.username', 'p.title', 'no.created_at', 'p.id')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p.id = no.entity_id')
            ->innerJoin('no.notificationChange', 'ch')
            ->innerJoin('ch.actor', 'actor')
            ->andWhere('n.notifier = :user_id')
            ->andWhere('no.entity_type_id = :entity_type_id')
            ->setParameter('user_id', $notifier_id)
            ->setParameter('entity_type_id', $entity_type_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults(100);
        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @param int $entity_type_id
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierIdWithComment(int $notifier_id, int $entity_type_id)
    {
        // TODO: use the entity_type and entity_type_id to group notifications
        // in the blog post the fields to get are: notificationObjectId, entity_type_id, entity_id, notifier_id
        // need to get the comment, the actor, (the notifier ?)
        $qb = $this->createQueryBuilder('no')
            ->select('actor.username', 'no.created_at')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'c.id = no.entity_id')
            ->innerJoin('no.notificationChange', 'ch')
            ->innerJoin('ch.actor', 'actor')
            ->andWhere('n.notifier = :user_id')
            ->andWhere('no.entity_type_id = :entity_type_id')
            ->setParameter('entity_type_id', $entity_type_id)
            ->setParameter('user_id', $notifier_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults(100);
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierIdWithBookmark(int $notifier_id)
    {
        $qb = $this->createQueryBuilder('no')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\Bookmark', 'b', Join::WITH, 'b.id = no.entity_id')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $notifier_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults(100);
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * This returns the
     * @param int $user_id
     * @return mixed
     */
    public function findNotificationsDetailsByNotifierId(int $user_id)
    {
        return $this->createQueryBuilder('no')
            ->select('no.entity_type_id')
            ->innerJoin('App\Entity\Notification', 'n', Join::WITH, 'n.notificationObject = no')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $user_id)
//            ->orderBy('no.created_at', 'DESC')
            ->groupBy('no.entity_type_id')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?NotificationObject
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
