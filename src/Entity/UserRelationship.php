<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRelationshipRepository")
 * @ORM\EntityListeners({"App\Listeners\UserRelationshipListener"})
 */
class UserRelationship
{
    const FRIENDREQUEST_TYPE_ID = 'friendrequest';

    const FRIENDAPPROVAL_TYPE_ID = 'friendapproval';

    const PENDING = 'pending';

    const FRIEND = 'friend';

    const BLOCK = 'block';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "pending"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="friends")
     */
    private $relatingUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $relatedUser;


    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRelatingUser(): ?User
    {
        return $this->relatingUser;
    }

    public function setRelatingUser(?User $relatingUser): self
    {
        $this->relatingUser = $relatingUser;

        return $this;
    }

    public function getRelatedUser(): ?User
    {
        return $this->relatedUser;
    }

    public function setRelatedUser(?User $relatedUser): self
    {
        $this->relatedUser = $relatedUser;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
