<?php 

namespace App\Services;

use App\Entity\Post;
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
     * @param  string $avatar [description]
     * @return string [type] [description]
     */
    public function getUserAvatar(string $avatar) : string
    {
        if ($avatar === 'avatar.png')
        {
            return $this->packages->getUrl('assets/img/') . $avatar;
        }
        else
        {
            return $this->packages->getUrl('uploads/avatars/') . $avatar;
        }
    }

    /**
     * Check whether the user user is the owner of the given post
     * @param Post $post
     * @return bool
     */
    public function checkPostOwnership(Post $post) : bool
    {
        return $this->security->getUser() === $post->getUser();
    }
}