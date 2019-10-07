<?php

namespace App\Tests;

use App\Entity\Post;
use App\Entity\User;
use App\Services\UserManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Security;

class UserManagerTest extends WebTestCase
{
    /** @var UserManager $userManager */
    private $userManager;

    /** @var Post $post */
    private $post;

    /** @var User $user */
    private $user;

    protected function setUp(): void
    {
        $security = $this->createMock(Security::class);
        $packages = $this->createMock(Packages::class);

        $this->userManager = new UserManager($security, $packages);

        $this->post = new Post();
        $this->user = new User();

        $this->user->setUsername("OverseasMedia");
        $this->post->setUser($this->user);
    }

    public function testOwnerShipOfPost()
    {
        $result = $this->userManager->checkPostOwnership($this->post);
        $this->assertEquals(false, $result);
        $this->assertSame($this->user, $this->post->getUser());
    }

    public function testGetUserWithNoAvatar()
    {
        var_dump("First");
        $this->assertEquals($this->userManager->getUserAvatar($this->user), '/assets/img/avatar.png');
    }
    // TODO: fix this
//    public function testGetUserWithAvatar()
//    {
//        var_dump("second");
//        $this->user->setAvatar('customavatar.png');
//        var_dump($this->userManager->getUserAvatar($this->user));
//        $this->assertEquals($this->userManager->getUserAvatar($this->user), '/uploads/avatars/customavatar.png');
//    }
}
