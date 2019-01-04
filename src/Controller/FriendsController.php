<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRelationship;
use App\Repository\UserRelationshipRepository;
use App\Services\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Gos\Bundle\WebSocketBundle\Topic\TopicManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Services\UserManager;

/**
 * @Route("/friends", name="friends.")
 * @Security("is_granted('ROLE_USER')")
 * Class FriendsController
 * @package App\Controller
 */
class FriendsController extends AbstractController
{
    /**
     * @var PusherDecorator
     */
    private $pusher;

    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var NotificationManager
     */
    private $notificationManager;

    public function __construct(PusherDecorator $pusher, Packages $packages, UserManager $userManager, NotificationManager $notificationManager)
    {
        $this->userManager = $userManager;
        $this->pusher = $pusher;
        $this->packages = $packages;
        $this->notificationManager = $notificationManager;
    }


    /**
     * @Route("/friends", name="friends")
     */
    public function index()
    {
        return $this->render('friends/index.html.twig', [
            'controller_name' => 'FriendsController',
        ]);
    }

    /**
     * Sends a friends request to the $user
     *
     * @Route("/{id}/add", name="addAsFriend")
     * @param User $user
     * @param UserRelationshipRepository $userRelationshipRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addAsFriend(User $user, UserRelationshipRepository $userRelationshipRepository, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(User::IS_AUTHENTICATED_FULLY);
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // NOTE: check if the user is logged in
        if (!$currentUser instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
        // NOTE: the user cannot add himself
        if ($this->getUser() === $user) {
            return $this->json([
                'type' => 'error',
                'message' => 'You cannot add yourself as a friend! that\'s weird man'
            ]);
        }

        // Check if the users are already friends
        $relationship = $userRelationshipRepository->findByRelatingUserAndRelatedUser(
            $currentUser->getId(),
            $user->getId()
        );

        if (!$relationship) {
            // CODE HERE WAS MOVED TO THE FUNCTION

            $this->notificationManager->persistFriendshipNotification($currentUser, $user, UserRelationship::PENDING);

            $notification = [
                'action' => 'wants to add you as a friend',
                'avatar' => $currentUser->getAvatar(),
                'notifier' => $user->getUsername(),
                'url' => $this->generateUrl('friends.pending'),
                'username' => $currentUser->getUsername(),
            ];
            $this->notificationManager->sendNotification($user, $notification);
        }

        return $this->redirect($this->generateUrl('profile.userProfile', ['username' => $user->getUsername()]));
    }

    /**
     * @Route("/pending", name="pending")
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pending(UserRelationshipRepository $userRelationshipRepository)
    {
        $this->checkIfUserLoggedIn();
        // check if the user logged in
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $result = $userRelationshipRepository->findUsersWithTypePending($currentUser->getId());

        return $this->render('friends/pending.html.twig', [
            'pending' => $result,
        ]);
    }

    /**
     * @Route("/{id}/approve", name="approveRequest")
     * @param User $user
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function approveRequest(User $user, UserRelationshipRepository $userRelationshipRepository, EntityManagerInterface $entityManager)
    {
        // check if the given user is actually a user or not
        if (!$user instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
        $currentUser = $this->getUser();
        // get the userRelationship from the database and then change it's type
        $relationship = $userRelationshipRepository->findByRelatingUserAndRelatedUser(
            $user->getId(),
            $currentUser->getId()
        );
        // check if the record exists
        if ($relationship) {
            $relationship->setType(UserRelationship::FRIEND);

            $this->notificationManager->persistFriendshipNotification($currentUser, $user, UserRelationship::FRIEND);

            $entityManager->persist($relationship);

            $notification = [
                // this is for the real-time notification, for constructing the notification when it arrive
                // to the front'end
                'action' => 'is now in your friends list',
                'avatar' => $currentUser->getAvatar(),
                'notifier' => $user->getUsername(),
                'url' => $this->generateUrl('profile.userProfile', ['username' => $currentUser->getUsername()]),
                'username' => $currentUser->getUsername(),
            ];

            $this->notificationManager->sendNotification($user, $notification);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('profile.userProfile', ['username' => $user->getUsername()]));
        }

        return $this->json([
            'type' => 'error',
            'message' => 'Error! something happened!',
        ]);

    }

    /**
     * @Route("/{id}/reject", name="rejectRequest")
     * @param User $user
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function rejectRequest(User $user, UserRelationshipRepository $userRelationshipRepository, EntityManagerInterface $entityManager)
    {
        // check if the given user is actually a user or not
        if (!$user instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
        $currentUser = $this->getUser();
        // get the userRelationship from the database and then remove it
        $relationship = $userRelationshipRepository->findByRelatingUserAndRelatedUser(
            $user->getId(),
            $currentUser->getId()
        );
        // check if the record exists
        if ($relationship) {
            $entityManager->remove($relationship);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('friends.pending', ['id' => $user->getId()]));
        }

        return $this->json([
            'type' => 'error',
            'message' => 'Error! something happened!',
        ]);
    }

    /**
     * @Route("/{id}/remove", name="removeFriend")
     * @param User $user
     * @param UserRelationshipRepository $userRelationshipRepository
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeFriend(User $user, UserRelationshipRepository $userRelationshipRepository, EntityManagerInterface $entityManager)
    {
        // check if the given user is actually a user or not
        if (!$user instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
        $currentUser = $this->getUser();
        // get the userRelationship from the database and then remove it
        $relationship = $userRelationshipRepository->findByRelatingUserAndRelatedUser(
            $user->getId(),
            $currentUser->getId()
        );

        $biDirectionalRelationship = $userRelationshipRepository->findByRelatingUserAndRelatedUser(
            $currentUser->getId(),
            $user->getId()
        );
        // check if the record exists
        if ($relationship && $biDirectionalRelationship) {
            // TODO: remove the two records from the db
            $entityManager->remove($relationship);
            $entityManager->remove($biDirectionalRelationship);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('profile.userProfile', ['username' => $user->getUsername()]));
        }

        return $this->json([
            'type' => 'error',
            'message' => 'Error! something happened!',
        ]);
    }

    /**
     * Check if the user is logged in, if not will redirect him to the login page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function checkIfUserLoggedIn()
    {
        if (!$this->getUser() instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
    }
}
