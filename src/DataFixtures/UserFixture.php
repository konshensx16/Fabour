<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
            $this->encoder->encodePassword($user, '0000')
        );
        $user->setEmail('no-reply@overseas.media');

        $user1 = new User();
        $user1->setUsername('admin1');
        $user1->setPassword(
            $this->encoder->encodePassword($user1, '0000')
        );
        $user1->setEmail('no-reply1@overseas.media');

        $manager->persist($user1);
        $manager->persist($user);

        $manager->flush();
    }
}