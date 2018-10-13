<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoriesFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // NOTE: in case of sub-categories, remember to set the cascade persist so i don't have
        //      to persist the created categories too (Winning!)

        $category = new Category('Arts & Entertainment', 'arts-and-entertainment');
        $category1 = new Category('Industry', 'industry');
        $category2 = new Category('Innovation & Tech', 'innovation-and-tech');
        $category3 = new Category('Life', 'life');
        $category4 = new Category('Society', 'society');

        $manager->persist($category);
        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->persist($category4);

        $manager->flush();
    }
}
