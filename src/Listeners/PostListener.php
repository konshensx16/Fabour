<?php

    namespace App\Listeners;

    use App\Entity\Attachment;
    use App\Entity\Post;
    use App\Entity\User;
    use App\Repository\AttachmentRepository;
    use App\Services\AttachmentManager;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\ORM\Event\PreUpdateEventArgs;
    use Symfony\Component\Security\Core\Security;


    class PostListener
    {
        // i need to get the current logged in user
        /**
         * @var Security
         */
        private $security;
        /**
         * @var EntityManagerInterface
         */
        private $entityManager;
        /**
         * @var AttachmentManager
         */
        private $attachmentManager;
        /**
         * @var AttachmentRepository
         */
        private $attachmentRepository;

        public function __construct(Security $security,
                                    EntityManagerInterface $entityManager,
                                    AttachmentManager $attachmentManager,
                                    AttachmentRepository $attachmentRepository
        )
        {
            $this->security = $security;
            $this->entityManager = $entityManager;
            $this->attachmentManager = $attachmentManager;
            $this->attachmentRepository = $attachmentRepository;
        }

        public function prePersist(Post $post, LifecycleEventArgs $args)
        {
            // set the current logged in user to the
            // nor really sure if i want to check if the user is logged in since this page is only
            // accessible to authenticated users
            $currentUser = $this->security->getUser();
            if ($currentUser instanceof User) {
                // well i think this is fair enough and should work for now!
                // maybe set the time too
                $post->setCreatedAt(new \DateTime());
                $post->setUser($currentUser);
            }
        }

        /**
         * @param Post $post
         * @param PreUpdateEventArgs $args
         * @throws \Exception
         */
        public function preUpdate(Post $post, PreUpdateEventArgs $args)
        {
            // check if the content has changed
            if ($args->hasChangedField('content')) {
                // TODO: get the diff between the two contents (original vs old)

                $matches = [];
                $regex = "~uploads/attachments/[a-zA-Z0-9]+\.\w+~";
                if (preg_match_all($regex, $args->getNewValue('content'), $matches) > 0) {
                    $filenames = array_map(function ($match) {
                        return basename($match);
                    }, $matches[0]);

                    $recordsToRemove = $this->attachmentRepository->findFilenamesToRemove($filenames, $post->getId()->toString());

                    /** @var Attachment $record */
                    foreach ($recordsToRemove as $record) {
                        // remove the file from the DB
                        $this->entityManager->getConnection()->beginTransaction();
                        $this->entityManager->remove($record);
                        // remove the file that needs to be removed using the attachment manager
                        $this->attachmentManager->removeAttachment($record->getFilename());
                        $this->entityManager->flush();
                        $this->entityManager->commit();
                    }
                }
                else if ($matches) // if i have attachments but the new content have none, then just remove everything
                {
                    $this->removeAllPostAttachments($post);
                }

            }
        }

        /**
         * @param Post $post
         * @throws \Exception
         */
        public function removeAllPostAttachments(Post $post)
        {
            if ($post->getAttachments()->count()) {
                foreach ($post->getAttachments() as $attachment)
                {
                    $entity = $this->entityManager->merge($attachment);
                    $this->entityManager->remove($entity);
                    $this->attachmentManager->removeAttachment($attachment->getFilename());
                }
                $this->entityManager->flush();
            }
        }

        /**
         * @param Post $post
         * @param LifecycleEventArgs $args
         * @throws \Exception
         */
        public function postRemove(Post $post, LifecycleEventArgs $args)
        {
            $this->removeAllPostAttachments($post);
        }
    }