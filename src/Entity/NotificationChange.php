<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationChangeRepository")
 */
class NotificationChange
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notificationChanges")
     */
    private $actor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\NotificationObject", inversedBy="notificationChange")
     */
    private $notificationObject;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getNotificationObject(): ?NotificationObject
    {
        return $this->notificationObject;
    }

    public function setNotificationObject(?NotificationObject $notificationObject): self
    {
        $this->notificationObject = $notificationObject;

        return $this;
    }
}
