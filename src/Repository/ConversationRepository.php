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
        )// end of where
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
     * @param int $conversation_id
     * @param int $user_id
     * @return bool|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findUnreadMessagesCount(int $conversation_id, int $user_id)
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(_res.id) AS unread
            FROM (
              SELECT m.id
              FROM message m
              WHERE m.conversation_id = :conversation_id
              AND m.recipient_id = :user_id
              AND m.read_at IS NULL
              ORDER BY m.id DESC
              LIMIT 10
            ) AS _res
        ';

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':conversation_id' => $conversation_id,
            ':user_id' => $user_id
        ]);

        return $statement->fetchColumn(0);
    }

    /**
     * Updates the messages read_at using the conversation_id
     * @param int $conversation_id
     * @param int $user_id
     * @return mixed
     */
    public function updateMessagesReadAt(int $conversation_id, int $user_id)
    {
        // NOTE: i need to only update the other messages and not mine
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('App\Entity\Message', 'm')
            ->set('m.read_at', ':date')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('m.conversation', ':conversation_id'),
                    $qb->expr()->eq('m.recipient', ':user_id')
                )
            )
            ->setParameter('conversation_id', $conversation_id)
            ->setParameter('date', (new \DateTime())->format('Y-m-d H:m:s'))
            ->setParameter('user_id', $user_id)
        ;
        return $qb->getQuery()->execute();
    }

    /**
     * Returns a list containing the latest 20|limit messages for a given conversation
     * @param int $conversation_id
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findLatestMessagesByConversationIdWithLimit(int $conversation_id)
    {
        $connection = $this->getEntityManager()->getConnection();

        $sqlQuery = '
            SELECT m.*, sender.username as senderUsername, sender.avatar as senderAvatar, recipient.username as recipientUsername, recipient.avatar as recipientAvatar
            FROM message m
            INNER JOIN user sender
            ON sender.id = m.sender_id
            INNER JOIN user recipient
            ON recipient.id = m.recipient_id
            WHERE m.conversation_id = :conversation_id
            ORDER BY id DESC
            LIMIT 20
        ';

        $statement = $connection->prepare($sqlQuery);
        $statement->execute([
            ':conversation_id' => $conversation_id
        ]);

        return $statement->fetchAll();
    }

    /**
     * Returns a list of 20 messages for a given conversation using an offset
     * @param int $conversation_id
     * @param int $offset
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findPreviousMessageByConversationIdWithOffset(int $conversation_id, int $offset)
    {
        $connection = $this->getEntityManager()->getConnection();

        $sqlQuery = '
            SELECT m.*, sender.username as senderUsername, sender.avatar as senderAvatar, recipient.username as recipientUsername, recipient.avatar as recipientAvatar
            FROM message m
            INNER JOIN user sender
            ON sender.id = m.sender_id
            INNER JOIN user recipient
            ON recipient.id = m.recipient_id
            WHERE m.conversation_id = :conversation_id
            ORDER BY id DESC
            LIMIT 20
            OFFSET '.$offset.'
        ';

        $statement = $connection->prepare($sqlQuery);
        $statement->execute([
            ':conversation_id'  => $conversation_id,
        ]);

        return $statement->fetchAll();
    }

    /**
     * Return the count of unread messages
     * @param int $user_id (this is recipient which means the current logged in user)
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getUnreadMessagesCount(int $user_id)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select($qb->expr()->count('c.id'))
            ->innerJoin('c.messages', 'm', Join::WITH, 'c.id = m.conversation')
//            ->innerJoin('m.recipient', 'u', Join::WITH, 'm.recipient = u')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('m.recipient', ':user_id'),
                    $qb->expr()->isNull('m.read_at')
                )
            )
            ->setParameter('user_id', $user_id)
        ;

        return $qb->getQuery()->getSingleScalarResult();

    }
}
