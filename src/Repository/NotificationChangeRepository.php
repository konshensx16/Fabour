<?php

namespace App\Repository;

use App\Entity\NotificationChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NotificationChange|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationChange|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationChange[]    findAll()
 * @method NotificationChange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationChangeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NotificationChange::class);
    }

//    /**
//     * @return NotificationChange[] Returns an array of NotificationChange objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NotificationChange
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
