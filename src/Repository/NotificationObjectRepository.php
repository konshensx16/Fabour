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

    public function findNotificationsByNotifiedId(int $notifier_id)
    {
        $qb = $this->createQueryBuilder('no')
            ->innerJoin('no.notification', 'n', Join::WITH, 'no.id = n.notificationObject')
            ->andWhere('n.notifier = :user_id')
            ->setParameter('user_id', $notifier_id)
            ->orderBy('no.created_at', 'DESC')
            ->setMaxResults(100);
        dump($qb->getQuery()->getSQL());
        return $qb
            ->getQuery()
            ->getResult();
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
