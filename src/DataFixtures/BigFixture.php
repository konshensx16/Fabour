<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BigFixture extends Fixture
{
    private $encoder;

    /** @var UserRepository $userRepository */
    private $userRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, UserRepository $userRepository, CategoryRepository $categoryRepository)
    {
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function load(ObjectManager $manager)
    {
        $category = new Category('Arts & Entertainment', 'arts-and-entertainment');
        $category1 = new Category('Industry', 'industry');
        $category2 = new Category('Innovation & Tech', 'innovation-and-tech');
        $category3 = new Category('Life', 'life');
        $category4 = new Category('Society', 'society');

        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
            $this->encoder->encodePassword($user, '0000')
        );
        $user->setEmail('no-reply@overseas.media');
        $user->setAbout('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla luctus non turpis ac vulputate. Fusce quis risus consectetur, venenatis enim facilisis, interdum orci. Suspendisse dignissim rhoncus sodales. Nunc rhoncus, dolor sed vestibulum egestas, dolor lacus cursus dolor, eget molestie ipsum est nec est. Cras ornare et ante nec commodo. Quisque tristique tellus et diam cursus ultricies. Fusce non sem mattis, tincidunt tortor fringilla, mollis enim.');
        $user->setCreatedAt(new \DateTime());
        $user->setLastSeen(new \DateTime());

        $user1 = new User();
        $user1->setUsername('admin1');
        $user1->setPassword(
            $this->encoder->encodePassword($user1, '0000')
        );
        $user1->setEmail('no-reply1@overseas.media');
        $user1->setAbout('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla luctus non turpis ac vulputate. Fusce quis risus consectetur, venenatis enim facilisis, interdum orci. Suspendisse dignissim rhoncus sodales. Nunc rhoncus, dolor sed vestibulum egestas, dolor lacus cursus dolor, eget molestie ipsum est nec est. Cras ornare et ante nec commodo. Quisque tristique tellus et diam cursus ultricies. Fusce non sem mattis, tincidunt tortor fringilla, mollis enim.');
        $user1->setCreatedAt(new \DateTime());
        $user1->setLastSeen(new \DateTime());

        $post = new Post();

        $post->setTitle('First post on the blog1');
        $post->setContent('<h1>Lorem ipsum dolor sit amet consectetuer adipiscing 
                            elit</h1>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa 
                            <strong>strong</strong>. Cum sociis natoque penatibus 
                            et magnis dis parturient montes, nascetur ridiculus 
                            mus. Donec quam felis, ultricies nec, pellentesque 
                            eu, pretium quis, sem. Nulla consequat massa quis 
                            enim. Donec pede justo, fringilla vel, aliquet nec, 
                            vulputate eget, arcu. In enim justo, rhoncus ut, 
                            imperdiet a, venenatis vitae, justo. Nullam dictum 
                            felis eu pede <a class="external ext" href="#">link</a> 
                            mollis pretium. Integer tincidunt. Cras dapibus. 
                            Vivamus elementum semper nisi. Aenean vulputate 
                            eleifend tellus. Aenean leo ligula, porttitor eu, 
                            consequat vitae, eleifend ac, enim. Aliquam lorem ante, 
                            dapibus in, viverra quis, feugiat a, tellus. Phasellus 
                            viverra nulla ut metus varius laoreet. Quisque rutrum. 
                            Aenean imperdiet. Etiam ultricies nisi vel augue. 
                            Curabitur ullamcorper ultricies nisi.</p>
                            <h1>Lorem ipsum dolor sit amet consectetuer adipiscing 
                            elit</h1>
                            <h2>Aenean commodo ligula eget dolor aenean massa</h2>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa. 
                            Cum sociis natoque penatibus et magnis dis parturient 
                            montes, nascetur ridiculus mus. Donec quam felis, 
                            ultricies nec, pellentesque eu, pretium quis, sem.</p>
                            <h2>Aenean commodo ligula eget dolor aenean massa</h2>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa. 
                            Cum sociis natoque penatibus et magnis dis parturient 
                            montes, nascetur ridiculus mus. Donec quam felis, 
                            ultricies nec, pellentesque eu, pretium quis, sem.</p>
                            <ul>
                              <li>Lorem ipsum dolor sit amet consectetuer.</li>
                              <li>Aenean commodo ligula eget dolor.</li>
                              <li>Aenean massa cum sociis natoque penatibus.</li>
                            </ul>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa. 
                            Cum sociis natoque penatibus et magnis dis parturient 
                            montes, nascetur ridiculus mus. Donec quam felis, 
                            ultricies nec, pellentesque eu, pretium quis, sem.</p>
                            <form action="#" method="post">
                              <fieldset>
                                <label for="name">Name:</label>
                                <input type="text" id="name" placeholder="Enter your 
                            full name" />
                            
                                <label for="email">Email:</label>
                                <input type="email" id="email" placeholder="Enter 
                            your email address" />
                            
                                <label for="message">Message:</label>
                                <textarea id="message" placeholder="What\'s on your 
                            mind?"></textarea>
                            
                                <input type="submit" value="Send message" />
                            
                              </fieldset>
                            </form>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa. 
                            Cum sociis natoque penatibus et magnis dis parturient 
                            montes, nascetur ridiculus mus. Donec quam felis, 
                            ultricies nec, pellentesque eu, pretium quis, sem.</p>
                            <table class="data">
                              <tr>
                                <th>Entry Header 1</th>
                                <th>Entry Header 2</th>
                                <th>Entry Header 3</th>
                                <th>Entry Header 4</th>
                              </tr>
                              <tr>
                                <td>Entry First Line 1</td>
                                <td>Entry First Line 2</td>
                                <td>Entry First Line 3</td>
                                <td>Entry First Line 4</td>
                              </tr>
                              <tr>
                                <td>Entry Line 1</td>
                                <td>Entry Line 2</td>
                                <td>Entry Line 3</td>
                                <td>Entry Line 4</td>
                              </tr>
                              <tr>
                                <td>Entry Last Line 1</td>
                                <td>Entry Last Line 2</td>
                                <td>Entry Last Line 3</td>
                                <td>Entry Last Line 4</td>
                              </tr>
                            </table>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing 
                            elit. Aenean commodo ligula eget dolor. Aenean massa. 
                            Cum sociis natoque penatibus et magnis dis parturient 
                            montes, nascetur ridiculus mus. Donec quam felis, 
                            ultricies nec, pellentesque eu, pretium quis, sem.</p>');

        $post->setCreatedAt(new \DateTime());
        $post->setUser(
            $user
        );

        $post->setCategory(
            $category
        );

        $manager->persist($category);
        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->persist($category4);

        $manager->persist($user1);
        $manager->persist($user);

        $manager->persist($post);

        $manager->flush();
    }
}
