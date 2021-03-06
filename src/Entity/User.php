<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\EntityListeners({"App\Listeners\UserListener"})
 * @ORM\Table(name="user", indexes={@ORM\Index(name="user_index", columns={"id", "username", "email"})})
 */
class User implements UserInterface, \Serializable, EquatableInterface
{

    // TODO: THESE SHOULD NOT BE HERE THEY HAVE NOTHING TO DO WITH THE USER
    //      THEY SHOULD BE IN AUTH OR SECURITY CLASS
    public const IS_AUTHENTICATED_FULLY = 'IS_AUTHENTICATED_FULLY';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *     min = 6,
     *     minMessage = "Password length is too short, has to be 6 characters minimum"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatar = '/assets/img/avatar.png';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twitter;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $last_seen;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $views_counter = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPasswordReset;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $resetPasswordToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="user", cascade={"persist", "remove"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bookmark", mappedBy="user", cascade={"persist", "remove"})
     */
    private $bookmarks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRelationship", mappedBy="relatingUser")
     */
    private $friends;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\NotificationChange", mappedBy="actor")
     */
    private $notificationChanges;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="recipient")
     */
    private $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Conversation", mappedBy="first_user")
     */
    private $conversations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $facebookID;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $facebookAccessToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $googleID;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $googleAccessToken;

//    /**
//     * @ORM\Column(type="string", length=255, nullable=true)
//     * @var string
//     */
//    private $twitterID;

//    /**
//     * @ORM\Column(type="string", length=255, nullable=true)
//     * @var string
//     */
//    private $twitterAccessToken;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->friends = new ArrayCollection();
        $this->notificationChanges = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->lastPasswordReset = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles()
    {
        return [
            'ROLE_USER',
            'ROLE_ADMIN'
        ];
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->locale
        ]);
    }

    public function unserialize($string)
    {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->locale
            ) = unserialize($string, ['allowed_classes' => false]);
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof self) {
            if ($user->getLocale() != $this->locale) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);

        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->username;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(string $about): self
    {
        $this->about = $about;

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

    public function getLastSeen(): ?\DateTimeInterface
    {
        return $this->last_seen;
    }

    public function setLastSeen(\DateTimeInterface $last_seen): self
    {
        $this->last_seen = $last_seen;

        return $this;
    }

    public function getViewsCounter(): ?int
    {
        return $this->views_counter;
    }

    public function setViewsCounter(int $views_counter): self
    {
        $this->views_counter = $views_counter;

        return $this;
    }

    /**
     * @return Collection|Bookmark[]
     */
    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setUser($this);
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        if ($this->bookmarks->contains($bookmark)) {
            $this->bookmarks->removeElement($bookmark);
            // set the owning side to null (unless already changed)
            if ($bookmark->getUser() === $this) {
                $bookmark->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserRelationship[]
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(UserRelationship $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends[] = $friend;
            $friend->setRelatingUser($this);
        }

        return $this;
    }

    public function removeFriend(UserRelationship $friend): self
    {
        if ($this->friends->contains($friend)) {
            $this->friends->removeElement($friend);
            // set the owning side to null (unless already changed)
            if ($friend->getRelatingUser() === $this) {
                $friend->setRelatingUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|NotificationChange[]
     */
    public function getNotificationChanges(): Collection
    {
        return $this->notificationChanges;
    }

    public function addNotificationChange(NotificationChange $notificationChange): self
    {
        if (!$this->notificationChanges->contains($notificationChange)) {
            $this->notificationChanges[] = $notificationChange;
            $notificationChange->setActor($this);
        }

        return $this;
    }

    public function removeNotificationChange(NotificationChange $notificationChange): self
    {
        if ($this->notificationChanges->contains($notificationChange)) {
            $this->notificationChanges->removeElement($notificationChange);
            // set the owning side to null (unless already changed)
            if ($notificationChange->getActor() === $this) {
                $notificationChange->setActor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setRecipient($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): self
    {
        if ($this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->removeElement($receivedMessage);
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getRecipient() === $this) {
                $receivedMessage->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): self
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages[] = $sentMessage;
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $sentMessage): self
    {
        if ($this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->removeElement($sentMessage);
            // set the owning side to null (unless already changed)
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->setFirstUser($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            // set the owning side to null (unless already changed)
            if ($conversation->getFirstUser() === $this) {
                $conversation->setFirstUser(null);
            }
        }

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): self
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getLastPasswordReset(): ?\DateTimeInterface
    {
        return $this->lastPasswordReset;
    }

    public function setLastPasswordReset(?\DateTimeInterface $lastPasswordReset): self
    {
        $this->lastPasswordReset = $lastPasswordReset;

        return $this;
    }

    public function getFacebookAccessToken(): ?string
    {
        return $this->facebookAccessToken;
    }

    public function setFacebookAccessToken(string $facebookAccessToken): self
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    public function getFacebookID(): ?string
    {
        return $this->facebookID;
    }

    public function setFacebookID(?string $facebookID): self
    {
        $this->facebookID = $facebookID;

        return $this;
    }

    public function getGoogleID(): ?string
    {
        return $this->googleID;
    }

    public function setGoogleID(?string $googleID): self
    {
        $this->googleID = $googleID;

        return $this;
    }

    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    public function setGoogleAccessToken(?string $googleAccessToken): self
    {
        $this->googleAccessToken = $googleAccessToken;

        return $this;
    }

//    public function getTwitterID(): ?string
//    {
//        return $this->twitterID;
//    }
//
//    public function setTwitterID(?string $twitterID): self
//    {
//        $this->twitterID = $twitterID;
//
//        return $this;
//    }
//
//    public function getTwitterAccessToken(): ?string
//    {
//        return $this->twitterAccessToken;
//    }
//
//    public function setTwitterAccessToken(?string $twitterAccessToken): self
//    {
//        $this->twitterAccessToken = $twitterAccessToken;
//
//        return $this;
//    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
}