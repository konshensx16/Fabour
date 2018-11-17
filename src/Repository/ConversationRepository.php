<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Get just 15 conversations each time
     * @param $user_id
     * @param int $limit
     * @return mixed
     */
    public function findConversationsByUserId($user_id, $limit = 15)
    {
        return $this->createQueryBuilder('c')
            ->orWhere('c.first_user = :user_id')
            ->orWhere('c.second_user = :user_id')
            ->setParameter('user_id', $user_id)
            ->setMaxResults($limit)
            ->orderBy('c.created_at')
            ->getQuery()
            ->getResult();
    }

    /**
     * NOTE: the conversations should be unique if i handle all the cases
     * Meaning if the 'admin' is the first_user and 'ahmed' is the second user, there shouldn't be another record where they're swapped, 'ahmed' is the first user and 'admin' is the second, this should be considered a duplicate
     * Return a conversation where the
     * SQL: select * from Test where (first_user = 12 and second_user = 9) or (first_user = 9 and second_user = 12);
     * The above sql is tested and
     * @param int $first_user_id
     * @param int $second_user_id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findConversationByUsers(int $first_user_id, int $second_user_id)
    {
        $qb = $this->createQueryBuilder('c');
        // NOTE: try not to use native sql, a chance to master doctrine queries
        /*
         *     [php]
         *     // (u.type = ?1) AND (u.role = ?2)
         *     $expr->andX($expr->eq('u.type', ':1'), $expr->eq('u.role', ':2'));
         *
         */
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.first_user', ':first_user_id'),
                    $qb->expr()->eq('c.second_user', ':second_user_id')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('c.first_user', ':second_user_id'),
                    $qb->expr()->eq('c.second_user ', ':first_user_id')
                )
            ) // end of andX
        ) // end of where
//            ->andWhere('c.first_user = :first_user_id')// This wil be an expression im pretty sure what i did here isn't gonna workm just putting the bricks where they belong
//            ->andWhere('c.first_user = :second_user_id')
//            ->orWhere('c.second_user = :first_user_id')
//            ->andWhere('c.first_user = :second_user_id')
            ->setParameter('first_user_id', $first_user_id)
            ->setParameter('second_user_id', $second_user_id);


        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findUnreadMessagesCount(int $conversation_id)
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT _res.*
            FROM (
              SELECT m.id
              FROM message m
              INNER JOIN conversation c
              ON c.id = m.conversation_id
              WHERE m.conversation_id = 1
              ORDER BY m.id DESC
            ) AS _res
        ';

        $statement = $connection->prepare($sql);
        $statement->execute([':conversation_id' => $conversation_id]);

        return $statement->fetchAll();
    }

//    /**
//     * @return Conversation[] Returns an array of Conversation objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
