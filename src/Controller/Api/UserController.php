<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Services\DateManager;
use App\Services\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/api/user", name="api.user.")
 * Class UserController
 * @package App\Controller\Api
 */
class UserController extends AbstractController
{

    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var DateManager
     */
    private $dateManager;

    public function __construct(Serializer $serializer, DateManager $dateManager)
    {
        $this->serializer = $serializer;
        $this->dateManager = $dateManager;
    }

    /**
     * @Route("/{id}", name="getUser", options={"expose"=true})
     * @param $id
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function user($id, UserRepository $userRepository)
    {
        $before = microtime(true);
        $currentUser = $this->getUser();

        if (!($currentUser instanceof UserInterface)) {
            throw new AuthenticationException();
        }
        // TODO: maybe check if the user are friends before letting the user accessing the last seen field

        $user = $userRepository->find($id);

        if (is_null($user)) {
            return $this->json([
                'type' => 'error',
                'messages' => 'user was not found'
            ]);
        } else {
            // TODO: turn the object into an array
            $array = [
                'username' => $user->getUsername(),
                'avatar' => $user->getAvatar(),
                'last_seen' => $this->dateManager->timeAgo($user->getLastSeen())
            ];

            $after = microtime(true);

            // TODO: Set the last seen
            return $this->json([
                'user' => $array,
                'took' => ($after - $before)
            ]);
        }
    }
}
