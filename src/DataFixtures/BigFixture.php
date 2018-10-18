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
        $category->subCategory('Art', 'art');
        $category->subCategory('Bookmarks', 'bookmarks');
        $category->subCategory('Comics', 'comics');
        $category->subCategory('Culture', 'culture');
        $category->subCategory('Film', 'film');
        $category->subCategory('Food', 'food');
        $category->subCategory('Gaming', 'gaming');
        $category->subCategory('Humour', 'humour');
        $category->subCategory('Internet Culture', 'internet-culture');
        $category->subCategory('Lit', 'lit');
        $category->subCategory('Music', 'music');
        $category->subCategory('Style', 'style');
        $category->subCategory('True Crime', 'true-crime');
        $category->subCategory('TV', 'tv');
        $category->subCategory('Writing', 'writing');

        $category1 = new Category('Industry', 'industry');
        $category1->subCategory('Business', 'business');
        $category1->subCategory('Design', 'design');
        $category1->subCategory('Economy', 'economy');
        $category1->subCategory('Startups', 'startups');
        $category1->subCategory('Freelancing', 'freelancing');
        $category1->subCategory('Leadership', 'leadership');
        $category1->subCategory('Marketing', 'marketing');
        $category1->subCategory('Productivity', 'productivity');
        $category1->subCategory('Work', 'work');

        $category2 = new Category('Innovation & Tech', 'innovation-and-tech');
        $category2->subCategory('Artificial Intelligence', 'artificial-intelligence');
        $category2->subCategory('Blockchain', 'Blockchain');
        $category2->subCategory('Cryptocurrency', 'cryptocurrency');
        $category2->subCategory('Cybersecurity', 'cybersecurity');
        $category2->subCategory('Data Science', 'data-science');
        $category2->subCategory('Gadgets', 'gadgets');
        $category2->subCategory('Javascript', 'javascript');
        $category2->subCategory('Machine Learning', 'machine-learning');
        $category2->subCategory('Math', 'math');
        $category2->subCategory('Programming', 'programming');
        $category2->subCategory('Science', 'science');
        $category2->subCategory('Space', 'space');
        $category2->subCategory('Technology', 'technology');
        $category2->subCategory('Visual Design', 'visual-design');

        $category3 = new Category('Life', 'life');
        $category3->subCategory('Creativity', 'creativity');
        $category3->subCategory('Disability', 'disability');
        $category3->subCategory('Family', 'family');
        $category3->subCategory('Health', 'health');
        $category3->subCategory('Mental Health', 'mental-health');
        $category3->subCategory('Parenting', 'parenting');
        $category3->subCategory('Personal Finance', 'personal-finance');
        $category3->subCategory('Pets', 'pets');
        $category3->subCategory('Psychology', 'psychology');
        $category3->subCategory('Relationships', 'relationships');
        $category3->subCategory('Self', 'self');
        $category3->subCategory('Sexuality', 'sexuality');
        $category3->subCategory('Travel', 'travel');
        $category3->subCategory('Wellness', 'wellness');

        $category4 = new Category('Society', 'society');
        $category4->subCategory('Basic Income', 'basic-income');
        $category4->subCategory('Cities', 'cities');
        $category4->subCategory('Education', 'education');
        $category4->subCategory('Environment', 'environment');
        $category4->subCategory('Equality', 'equality');
        $category4->subCategory('Future','future');
        $category4->subCategory('History', 'history');
        $category4->subCategory('Justice', 'justice');
        $category4->subCategory('Language', 'language');
        $category4->subCategory('Media', 'media');
        $category4->subCategory('Philosophy', 'philosophy');
        $category4->subCategory('Politics', 'politics');
        $category4->subCategory('Race', 'race');
        $category4->subCategory('Religion', 'religion');
        $category4->subCategory('Transportation', 'transportation');
        $category4->subCategory('Women', 'women');
        $category4->subCategory('Men', 'men');
        $category4->subCategory('World', 'world');

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

        $user2 = new User();
        $user2->setUsername('jasmine');
        $user2->setPassword(
            $this->encoder->encodePassword($user2, '0000')
        );
        $user2->setEmail('jasmine@overseas.media');
        $user2->setAbout('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla luctus non turpis ac vulputate. Fusce quis risus consectetur, venenatis enim facilisis, interdum orci. Suspendisse dignissim rhoncus sodales. Nunc rhoncus, dolor sed vestibulum egestas, dolor lacus cursus dolor, eget molestie ipsum est nec est. Cras ornare et ante nec commodo. Quisque tristique tellus et diam cursus ultricies. Fusce non sem mattis, tincidunt tortor fringilla, mollis enim.');
        $user2->setCreatedAt(new \DateTime());
        $user2->setLastSeen(new \DateTime());

        $user3 = new User();
        $user3->setUsername('ahmed');
        $user3->setPassword(
            $this->encoder->encodePassword($user3, '0000')
        );
        $user3->setEmail('ahmed@overseas.media');
        $user3->setAbout('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla luctus non turpis ac vulputate. Fusce quis risus consectetur, venenatis enim facilisis, interdum orci. Suspendisse dignissim rhoncus sodales. Nunc rhoncus, dolor sed vestibulum egestas, dolor lacus cursus dolor, eget molestie ipsum est nec est. Cras ornare et ante nec commodo. Quisque tristique tellus et diam cursus ultricies. Fusce non sem mattis, tincidunt tortor fringilla, mollis enim.');
        $user3->setCreatedAt(new \DateTime());
        $user3->setLastSeen(new \DateTime());

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

        $manager->persist($user);
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);

        $manager->persist($post);

        $manager->flush();
    }
}
