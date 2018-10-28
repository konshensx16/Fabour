<?php
namespace App\Listeners;

use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class UserListener
{

    public function prePersist(User $user, LifecycleEventArgs $args)
    {
        $user->setCreatedAt(new \DateTime());
    }

   
   // public function preUpdate(User $user, PreUpdateEventArgs $args)
   //  {
   //  	dump($user);
   //  	dump($args);
   //  }

   //  public function prePersist(LifecycleEventArgs $args)
   //  {
   //      /**
   //       * @var Profile $entity
   //       */
   //      $entity = $args->getObject();
   //      if ($entity instanceof Profile)
   //      {
   //          $file = $entity->getAvatar();

   //          $filename = $this->_fileManager->uploadFile($file);

   //          $entity->setAvatar($filename);
   //      }
   //  }

   //  public function preUpdate(PreUpdateEventArgs $args)
   //  {
   //      dump($args);
   //      if ($args->hasChangedField('avatar'))
   //      {
   //          // upload the new file
   //          $entity = $args->getObject();
   //          if (is_null($args->getNewValue('avatar')))
   //          {
   //              return;
   //          }
   //          $filename = $this->_fileManager->uploadfile($args->getNewValue('avatar'));

   //          // remove the old file if any exists
   //          dump($args->getOldValue('avatar'));
   //          $this->_fileManager->removeFile($args->getOldValue('avatar'));
   //          $args->setNewValue('avatar', $filename);
   //      }
   //  }

   //  public function postRemove(LifecycleEventArgs $args)
   //  {
   //      // triggered after the removal from the database (after #flush)
   //      /**
   //       * @var Profile $entity
   //       */
   //      $entity = $args->getObject();

   //      $avatar = $entity->getAvatar();
   //      if (is_null($avatar))
   //      {
   //          return;
   //      }
   //      $this->_fileManager->removeFile($avatar);
   //  }

}