<?php

namespace App\Repository;

use App\Entity\NotificationObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @method NotificationObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationObject[]    findAll()
 * @method NotificationObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationObjectRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(RegistryInterface $registry, Security $security)
    {
        parent::__construct($registry, NotificationObject::class);
        $this->security = $security;
    }

    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @param int $limit
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierId(int $notifier_id, int $limit = 20)
    {
        // grouping by the entity id
        $qb = $this->createQueryBuilder('no')
            ->select('no.id', 'no.entity_id', 'no.entity_type_id', 'u.id', 'no.created_at')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\User', 'u', Join::WITH, 'n.notifier = u.id')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $notifier_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults($limit);
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
            ->select('actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id', 'no.id')
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
            ->select('actor.username', 'actor.avatar', 'no.created_at', 'no.id as comment_id', 'p.id as post_id', 'no.id')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'c.id = no.entity_id')
            ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = c.post')
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
            ->getResult();
    }


    /**
     * $notifier_id is the person who will be notified
     * @param int $notifier_id
     * @param int $entity_type_id
     * @return NotificationObject[] Returns an array of NotificationObject objects
     */

    public function findNotificationsByNotifierIdWithBookmark(int $notifier_id, int $entity_type_id)
    {
        $qb = $this->createQueryBuilder('no')
            ->select('no.id', 'p.title', 'actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
            ->innerJoin('App\Entity\Bookmark', 'b', Join::WITH, 'b.id = no.entity_id')
            ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = b.post')
            ->innerJoin('no.notificationChange', 'nc')
            ->innerJoin('nc.actor', 'actor')
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
     * This returns the
     * @param int $user_id
     * @param int $limit
     * @return mixed
     */
    public function findNotificationsDetailsByNotifierId(int $user_id, int $limit = 20)
    {
        return $this->createQueryBuilder('no')
            ->select('no.entity_type_id')
            ->innerJoin('App\Entity\Notification', 'n', Join::WITH, 'n.notificationObject = no')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $user_id)
//            ->orderBy('no.created_at', 'DESC')
            ->groupBy('no.entity_type_id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * This returns the
     * @param int $user_id
     * @param int $limit
     * @return mixed
     */
    public function findNotificationsDetailsByNotifierIdWithoutGroupBy(int $user_id, int $limit = 1000)
    {
        return $this->createQueryBuilder('no')
            ->select('no.entity_type_id', 'no.entity_id')
            ->innerJoin('App\Entity\Notification', 'n', Join::WITH, 'n.notificationObject = no')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $user_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    // TODO: this needs to change i can't keep hitting the db like that
    // i need something to make a single request to get all the data i need
    public function findOnePostByNotifierIdAndEntityTypeIdAndEntityId(int $notifier_id, int $entity_type_id = 1, int $entity_id)
    {
        try {
            return $this->createQueryBuilder('no')
                ->select('actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id')
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p.id = no.entity_id')
                ->innerJoin('no.notificationChange', 'ch')
                ->innerJoin('ch.actor', 'actor')
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->andWhere('no.entity_id = :entity_id')
                ->setParameter('user_id', $notifier_id)
                ->setParameter('entity_type_id', $entity_type_id)
                ->setParameter('entity_id', $entity_id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }
    }

    public function findOneCommentByNotifierIdAndEntityTypeIdAndEntityId(int $notifier_id, int $entity_id)
    {
        try {
            return $this->createQueryBuilder('no')
                ->select('actor.username', 'actor.avatar', 'no.created_at', 'c.id as comment_id', 'p.id as post_id')
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'c.id = no.entity_id')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = c.post')
                ->innerJoin('no.notificationChange', 'ch')
                ->innerJoin('ch.actor', 'actor')
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->andWhere('no.entity_id = :entity_id')
                ->setParameter('entity_type_id', 2)
                ->setParameter('user_id', $notifier_id)
                ->setParameter('entity_id', $entity_id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }

    }

    public function findOneBookmarkByNotifierIdAndEntityTypeIdAndEntityId(int $notifier_id, int $entity_id)
    {
        try {
            return $this->createQueryBuilder('no')
                ->select('p.title', 'actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id')
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                ->innerJoin('App\Entity\Bookmark', 'b', Join::WITH, 'b.id = no.entity_id')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = b.post')
                ->innerJoin('no.notificationChange', 'nc')
                ->innerJoin('nc.actor', 'actor')
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->andWhere('no.entity_id = :entity_id')
                ->setParameter('user_id', $notifier_id)
                ->setParameter('entity_type_id', 3)
                ->setParameter('entity_id', $entity_id)
                ->orderBy('no.created_at', 'DESC')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
        }
    }

    public function findCountCommentsForPost(int $post_id)
    {
        // additional note about the status, as of now i'm just using it to say if the notification was read(1) or not(0)
        //i should get multiple records 
        // dump($this->security->getUser()->getId()); 
        // use the entity_type_id to get just the comments (post_type_id = 1, comment_type_id = 2)
        $qb = $this->createQueryBuilder('no');
        return $qb->select($qb->expr()->count('no'))
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')        
                ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'no.entity_id = c.id')        
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'c.post = p.id')        
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->andWhere('p.id = :entity_id')
                ->andWhere('no.status = 1')
                ->setParameter('user_id', $this->security->getUser()->getId())
                ->setParameter('entity_type_id', 2)
                ->setParameter('entity_id', $post_id)
                ->getQuery()
                ->getSingleScalarResult()
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
