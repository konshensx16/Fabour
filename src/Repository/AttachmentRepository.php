<?php

namespace App\Repository;

use App\Entity\Attachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

/**
 * @method Attachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attachment[]    findAll()
 * @method Attachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttachmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Attachment::class);
    }

    /**
     * Remove a record from attachments using the filename
     * Q: Should i include the post_id (more security?)
     * @param string $filename
     *
     * @param int $post_id
     * @return mixed
     */
    public function deleteAttachmentByFilename(string $filename, int $post_id)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->delete()
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('a.filename', ':filename'),
                    $qb->expr()->eq('a.post', ':post_id')
                )
            )
            ->setParameter('filename', $filename)
            ->setParameter('post_id', $post_id)
        ;
        return $qb->getQuery()->execute();
    }

    /**
     * Gets the filenames that needs to be removed
     * @param array $filenames
     * @param int $post_id
     * @return mixed
     */
    public function findFilenamesToRemove(array $filenames, int $post_id)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select()
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('a.post', $post_id),
                    $qb->expr()->notIn('a.filename', $filenames)
                )
            )
        ;

        dump($qb->getQuery()->getSQL());

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Attachment[] Returns an array of Attachment objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attachment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
