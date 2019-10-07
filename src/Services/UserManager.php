<?php

namespace App\Services;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Security;

class UserManager
{
    /**
     * @var Packages
     */
    private $packages;
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security, Packages $packages)
    {
        $this->packages = $packages;
        $this->security = $security;
    }

    /**
     * Return the correct full url of the user avatar
     * @param User $user
     * @return string [type] [description]
     */
    public function getUserAvatar(User $user): string
    {
        if ($user->getAvatar() === 'avatar.png')
            return $this->packages->getUrl('assets/img/') . $user->getAvatar();
        return $this->packages->getUrl('uploads/avatars/') . $user->getAvatar();
    }

    /**
     * Check whether the user user is the owner of the given post
     * @param Post $post
     * @return bool
     */
    public function checkPostOwnership(Post $post): bool
    {
        return $this->security->getUser() === $post->getUser();
    }
}