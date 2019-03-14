<?php

namespace App\Repository;

use App\Entity\TutorialCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TutorialCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TutorialCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TutorialCategory[]    findAll()
 * @method TutorialCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TutorialCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TutorialCategory::class);
    }

    // /**
    //  * @return TutorialCategory[] Returns an array of TutorialCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TutorialCategory
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
