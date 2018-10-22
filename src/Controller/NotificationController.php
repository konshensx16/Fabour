<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications", name="notifications.")
 * Class NotificationController
 * @package App\Controller
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/all", name="all")
     * Not all of them just the last 100 tbh (maybe should just one week or something)
     * @param NotificationObjectRepository $notificationObjectRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(NotificationObjectRepository $notificationObjectRepository)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $result = $notificationObjectRepository->findNotificationsByNotifiedId($currentUser->getId());

        dump($result);

        return $this->render('notification/index.html.twig', [
            'controller_name' => 'NotificationController',
            'result' => $result
        ]);
    }
}
