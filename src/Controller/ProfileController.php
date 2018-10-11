<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param Request $request
     * @param $username
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfile(Request $request, $username, UserRepository $userRepository)
    {
        // server-side rendering no need to worry about sensitive information getting out!
        // If the user is not logged in he will be redirected to somewhere else!
        // TODO: redirect the user to the login page if note authenticated!
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // TODO: test this stuff further more, just to make sure everything is working fine!
        $user = null;
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
        }

        return $this->render('profile/userProfile.html.twig', [
            'profile' => $user
        ]);
    }

    /**
     * @Route("/edit/", name="edit")
     * @param Request $request
     * @param EntityManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(Request $request, EntityManager $manager)
    {
        // TODO: cehck if the user has enough permissions to change information
        // the logged in user should be able to change his info
        // TODO: implement this action
        $user = $this->getUser();

        $form = $this->createForm(UserFormType::class, $user);

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
