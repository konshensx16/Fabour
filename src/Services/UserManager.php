<?php 

namespace App\Services;

use Symfony\Component\Asset\Packages;

class UserManager 
{
	/**
     * @var Packages
     */
    private $packages; 

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Return the correct full url of the user avatar
     * @param  string $avatar [description]
     * @return [type]         [description]
     */
    public function getUserAvatar(string $avatar)
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
}