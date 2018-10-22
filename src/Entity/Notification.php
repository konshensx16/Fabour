<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\NotificationObject", inversedBy="notification")
     */
    private $notificationObject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $notifier;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

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

    public function getNotificationObject(): ?NotificationObject
    {
        return $this->notificationObject;
    }

    public function setNotificationObject(?NotificationObject $notificationObject): self
    {
        $this->notificationObject = $notificationObject;

        return $this;
    }

    public function getNotifier(): ?User
    {
        return $this->notifier;
    }

    public function setNotifier(?User $notifier): self
    {
        $this->notifier = $notifier;

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
}
