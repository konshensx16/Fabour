<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRelationshipRepository;
use App\Repository\UserRepository;
use function Couchbase\defaultDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/search", name="search.")
 * Class SearchController
 * @package App\Controller
 */
class SearchController extends AbstractController
{
    public function searchBar()
    {
        // TODO: create a separate Type for this if needed!
        // This needs an action (and couple of other stuff??)
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('search.handleSearch'),
        ])
            ->add('query', TextType::class, [
                'attr' => [
                    'placeholder' => 'Search'
                ],
                'required' => true,
            ])
            ->getForm();

        // TODO: the form_start and form_end are ruining the design! investigate
        return $this->render('search/searchbar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Search without any filters or anything
     * @Route("/{query?}/{filter?}", name="handleSearch")
     * @param Request $request
     * @param $query
     * @param $filter
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleSearch(Request $request, $query, $filter, PostRepository $postRepository, UserRepository $userRepository, UserRelationshipRepository $userRelationshipRepository)
    {
        // get the query from the request
        $currentUser = $this->getUser();
        if (!$currentUser instanceof UserInterface) {
            return false;
        }
        // if query is not defined then get the result from the request
        if (!$query) {
            $query = $request->get('form')['query'];
        }

        $posts = null;
        $users = null;
        $friends = null;

        $posts = $postRepository->findPostsByName($query);
        $users = $userRepository->findUsersByName($query);
        $friends = $userRelationshipRepository->findFriendsByUsername($currentUser->getId(), $query);


        if ($filter) {
            switch ($filter) {
                case 'post':
                    // get post
                    // users and friends just counts
                    $users = count($users);
                    $friends = count($friends);
                    break;
                case 'users':
                    // get the users
                    // friends and post just counts
                    $posts = count($posts);
                    $friends = count($friends);
                    break;
                case 'friends':
                    // get friends
                    // post and users just counts
                    $posts = count($posts);
                    $users = count($users);
                    break;
            }
        }
        else
        {
            $users = count($users);
            $friends = count($friends);
        }

        dump($users);


//        if ()
        return $this->render('search/results.html.twig', [
            'filter' => $filter ? $filter : 'post',
            'query' => $query,
            'post' => $posts,
            'users' => $users,
            'friends' => $friends
        ]);
    }
}
