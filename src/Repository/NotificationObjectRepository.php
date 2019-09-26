<?php

    namespace App\Repository;

    use App\Entity\NotificationObject;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\ORM\NonUniqueResultException;
    use Doctrine\ORM\Query;
    use Doctrine\ORM\Query\Expr\Join;
    use Doctrine\ORM\Query\ResultSetMapping;
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
                ->select('actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id as post_id', 'n.id')
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
                ->select('actor.username', 'actor.avatar', 'no.created_at', 'c.id as comment_id', 'p.id as post_id', 'n.id')
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
                ->select('n.id', 'p.title', 'actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id as post_id')
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
         * @param int $limit
         * @return mixed
         * @throws \Doctrine\DBAL\DBALException
         */
        public function findNotificationsDetailsByNotifierIdGroupByEntityId(int $limit = 4)
        {
            $connection = $this->getEntityManager()->getConnection();
            // NOTE: i only need to LIMIT in the sub-query
            // I NEED TO JOIN IN THE SUB QUERY TOO
            $sqlQuery = 'SELECT n0_.entity_type_id, COUNT(n0_.entity_type_id) AS theCount
                          FROM (SELECT noAlias.* FROM notification_object noAlias 
                              INNER JOIN notification n13_ 
                              ON (n13_.notification_object_id = noAlias.id)
                              WHERE n13_.notifier_id = :user_id 
                              ORDER BY noAlias.id 
                              DESC LIMIT 4) n0_
                        INNER JOIN notification n1_ 
                        ON (n1_.notification_object_id = n0_.id)
                        GROUP BY n0_.entity_type_id
                        ';

            $statement = $connection->prepare($sqlQuery);
            $statement->execute(['user_id' => $this->getCurrentUserId()]);

            return $statement->fetchAll();

            /*
            // i need to create a native sql since DQL doesn't support using sub-queries in the from clause.
            // NOTE: i need to ge th count of entity_type of just the 4 first result
            $qb = $this->createQueryBuilder('no');
            $query = $qb
                ->select($qb->expr()->count('no.entity_type_id'), 'no.entity_type_id')
//                ->from( // i need to get just the first four results and work on that NOTE: This is not possible
//                    $qb->select('no')
//                    ->setMaxResults($limit),
//                    'notification_object_alias'
//                )

                ->innerJoin('App\Entity\Notification', 'n', Join::WITH, 'n.notificationObject = no')
                ->andWhere('n.notifier = :user_id')
                ->setParameter('user_id', $this->getCurrentUserId())
//                ->orderBy('no.created_at', 'DESC')
                ->groupBy('no.entity_type_id')
                ->setMaxResults($limit);
            dump($query->getQuery()->getSQL());
            return $query->getQuery()
                ->getSQL();
            ;
            */
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

        /**
         * TODO: this is just for testing, improve or recreate later
         * @param int $limit
         * @return mixed
         * @throws \Doctrine\DBAL\DBALException
         */
        public function groupCommentsByPosts(int $limit = 4)
        {
            // TODO: fix the concatenation of the limit, i should bind the parameter no matter what.
            $connection = $this->getEntityManager()->getConnection();
            // NOTE: i need to make a sub queries that will get me the queries ?
            //      and then work on the result of that query (trying to make this work then improve it)
            // I have th elatest date now,
            // I need to get the latest user avatar
            // TODO: the avatar im getting is not the correct one, @critical fix requried
            // NOTE: i removed the actor fetching from the subquery and gonna move it to the main query
            // I think im just gona do a union and be done with this bullshit, fuck it it's just couple of records each time
            $sql = '
                SELECT _no.*
                FROM (
                    SELECT no.created_at, 
                            no.id as id, c.post_id, p.title, n.id as n_id, nc.id as nc_id
                    FROM notification_object no
                    INNER JOIN notification n
                    ON n.notification_object_id = no.id 
                    INNER JOIN notification_change nc
                    ON nc.notification_object_id = no.id 
                    INNER JOIN comment c
                    ON c.id = no.entity_id
                    INNER JOIN post p
                    ON p.id = c.post_id
                    INNER JOIN user u
                    ON n.notifier_id = u.id
                    WHERE n.notifier_id = :user_id
                    LIMIT '. $limit .'
                ) AS _no 
                ORDER BY _no.created_at DESC
            ';
            $statement = $connection->prepare($sql);
            $statement->execute([
                'user_id' => $this->getCurrentUserId(),
//                'limit_' => $limit
            ]);

            return $statement->fetchAll();

            // TODO: i need to include the avatar of the latest person to comment
            // TODO: also include the time of the latest notification
//            $qb = $this->createQueryBuilder('no');
//            return $qb->select($qb->expr()->count('no') . 'AS count', 'p.id', 'p.title')
//                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
//                ->innerJoin('no.notificationChange', 'nc', Join::WITH, 'no = nc.notificationObject')
//                ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'no.entity_id = c.id')
//                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'c.post = p.id')
//                ->andWhere('n.notifier = :user_id')
//                ->andWhere('no.entity_type_id = :entity_type_id')
//                ->andWhere('no.status = 1') // read notifications
//                ->setParameter('user_id', $this->getCurrentUserId())
//                ->setParameter('entity_type_id', 2)
//                ->groupBy('c.post')
//                ->setMaxResults($limit)
//                ->getQuery()
//                ->getResult();
        }


        /**
         * TODO: this is just for testing, improve or recreate later
         * @param int $post_id
         * @return mixed
         */
        public function findCountCommentsForPost(int $post_id)
        {
            // additional note about the status, as of now i'm just using it to say if the notification was read(1) or not(0)
            //i should get multiple records
            // dump($this->$this->getCurrentUserId());
            // use the entity_type_id to get just the comments (post_type_id = 1, comment_type_id = 2)
            $qb = $this->createQueryBuilder('no');
            try {
                return $qb->select($qb->expr()->count('no'))
                    ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                    ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'no.entity_id = c.id')
                    ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'c.post = p.id')
                    ->andWhere('n.notifier = :user_id')
                    ->andWhere('no.entity_type_id = :entity_type_id')
                    ->andWhere('p.id = :entity_id')
                    ->andWhere('no.status = 1')
                    ->setParameter('user_id', $this->getCurrentUserId())
                    ->setParameter('entity_type_id', 2)
                    ->setParameter('entity_id', $post_id)
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (NonUniqueResultException $e) {
            }
        }

        // TODO: change the parameter entity_type_id to be fetched from the services file
        public function findLatestPostNotifications(int $limit)
        {
            // this needs to get the limit from notifications and not from the posts
            $qb = $this->createQueryBuilder('no')
                ->select('actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id as post_id', 'n.id', 'n.status as status')
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p.id = no.entity_id')
                ->innerJoin('no.notificationChange', 'ch')
                ->innerJoin('ch.actor', 'actor')
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->setParameter('user_id', $this->getCurrentUserId())
                ->setParameter('entity_type_id', 1)// i need to change this to get the value from the services file
                ->orderBy('no.created_at', 'DESC')
                ->setMaxResults($limit);
            return $qb
                ->getQuery()
                ->getResult();
        }


        // TODO: change the entity_type_id to be fetched from the services file
        public function findLatestBookmarkNotifications(int $limit)
        {
            return $this->createQueryBuilder('no')
                ->select('p.title', 'actor.username', 'actor.avatar', 'p.title', 'no.created_at', 'p.id post_id', 'n.id', 'n.status as status')
                ->innerJoin('no.notification', 'n', Join::WITH, 'no = n.notificationObject')
                ->innerJoin('App\Entity\Bookmark', 'b', Join::WITH, 'b.id = no.entity_id')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p = b.post')
                ->innerJoin('no.notificationChange', 'nc')
                ->innerJoin('nc.actor', 'actor')
                ->andWhere('n.notifier = :user_id')
                ->andWhere('no.entity_type_id = :entity_type_id')
                ->setParameter('user_id', $this->getCurrentUserId())
                ->setParameter('entity_type_id', 3)
                ->orderBy('no.created_at', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        }

        /**
         * Gets the current logged in user ID using the security component
         */
        private function getCurrentUserId()
        {
            return $this->security->getUser()->getId();
        }

        public function findLatestComments(int $limit)
        {
            $qb = $this->createQueryBuilder('no');

            $qb
                ->select('n.id', 'c.content', 'notifier.username as notifier_username', 'actor.username', 'actor.avatar', 'no.created_at', 'p.title', 'p.id as post_id', 'n.status as status')
                ->innerJoin('App\Entity\Comment', 'c', Join::WITH, 'c.id = no.entity_id')
                ->innerJoin('App\Entity\Post', 'p', Join::WITH, 'p.id = c.post')
                ->innerJoin('no.notificationChange', 'nc')
                ->innerJoin('no.notification', 'n')
                ->innerJoin('n.notifier', 'notifier')
                ->innerJoin('nc.actor', 'actor')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('n.notifier', ':user_id'),
                        $qb->expr()->neq('nc.actor', ':user_id'),
                        $qb->expr()->eq('no.entity_type_id', ':entity_type_id')
                    )
                )
                ->setParameter('user_id', $this->getCurrentUserId())
                ->setParameter('entity_type_id', 2)
                ->addOrderBy('no.id', 'DESC')
            ;

            return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        }
    }
