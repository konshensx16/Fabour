<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationObjectRepository")
 */
class NotificationObject
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $entity_type_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $entity_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="notificationObject")
     */
    private $notification;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationChange", mappedBy="notificationObject")
     */
    private $notificationChange;

    public function __construct()
    {
        $this->notification = new ArrayCollection();
        $this->notificationChange = new ArrayCollection();
        $this->created_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityTypeId(): ?int
    {
        return $this->entity_type_id;
    }

    public function setEntityTypeId(int $entity_type_id): self
    {
        $this->entity_type_id = $entity_type_id;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entity_id;
    }

    public function setEntityId(int $entity_id): self
    {
        $this->entity_id = $entity_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
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

    /**
     * @return Collection|Notification[]
     */
    public function getNotification(): Collection
    {
        return $this->notification;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notification->contains($notification)) {
            $this->notification[] = $notification;
            $notification->setNotificationObject($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notification->contains($notification)) {
            $this->notification->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getNotificationObject() === $this) {
                $notification->setNotificationObject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|NotificationChange[]
     */
    public function getNotificationChange(): Collection
    {
        return $this->notificationChange;
    }

    public function addNotificationChange(NotificationChange $notificationChange): self
    {
        if (!$this->notificationChange->contains($notificationChange)) {
            $this->notificationChange[] = $notificationChange;
            $notificationChange->setNotificationObject($this);
        }

        return $this;
    }

    public function removeNotificationChange(NotificationChange $notificationChange): self
    {
        if ($this->notificationChange->contains($notificationChange)) {
            $this->notificationChange->removeElement($notificationChange);
            // set the owning side to null (unless already changed)
            if ($notificationChange->getNotificationObject() === $this) {
                $notificationChange->setNotificationObject(null);
            }
        }

        return $this;
    }
}
