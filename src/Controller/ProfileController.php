<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\UserFormType;
use App\Form\UserProfileType;
use App\Repository\UserRelationshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/profile", name="profile.")
 * Class ProfileController
 * @package App\Controller
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index()
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @Route("/{username?}", name="userProfile")
     * @param $username
     * @param UserRepository $userRepository
     * @param UserRelationshipRepository $userRelationshipRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function userProfile($username, UserRepository $userRepository, UserRelationshipRepository $userRelationshipRepository, EntityManagerInterface $entityManager)
    {
        // server-side rendering no need to worry about sensitive information getting out!
        // If the user is not logged in he will be redirected to somewhere else!
        // TODO: redirect the user to the login page if note authenticated!
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // TODO: Increment the views_counter for the user if someone is seeing this page
        // TODO: test this stuff further more, just to make sure everything is working fine!
        $user = null;
        $relationship = null;
        // if no username, display the current logged in user
        if (!$username)
        {
            $user = $this->getUser();
        }
        else
        {
            $user = $userRepository->findOneBy([
                'username' => $username
            ]);
            // TODO: check if the current user is friends with the $user
            $relationship = $userRelationshipRepository->findOneFriendById(
                $this->getUser()->getId(),
                $user->getId()
            );
        }

        if (!($user === $this->getUser())) {
            // INC the counter
            // TODO: only increment if not from the same IP address
            $views_counter = $user->getViewsCounter();
            $user->setViewsCounter(++$views_counter);

            $entityManager->persist($user);
            $entityManager->flush();
        }

        // TODO: Get the last five friends the user added
        $recentlyAddedFriends = $userRelationshipRepository->findFriendsWithLimitById($user->getId(), 5);

        return $this->render('profile/userProfile.html.twig', [
            'profile' => $user,
            'friends' => $userRelationshipRepository->findUsersWithTypeFriend($user->getId()),
            'isFriend' => (bool)$relationship,
            'recentFriends' => $recentlyAddedFriends
        ]);
    }

    /**
     * @Route("/edit/", name="edit")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $manager)
    {
        // TODO: check if the user has enough permissions to change information
        // the logged in user should be able to change his info

        // TODO: implement this action
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        $form = $this->createForm(UserProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // TODO: Change this to a transaction maybe? not really sure why would this fail!
            $manager->persist($user);
            $manager->flush();
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/author", name="author")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function author(Request $request, Post $post)
    {
        return $this->render('profile/author.html.twig', [
            // TODO: this will return the entire username i might need to send just the things i need
            'author' => $post->getUser()
        ]);
    }
}
