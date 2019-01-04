<?php

namespace App\Controller\Api;

use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/api/messages", name="api.messages.")
 * @Security("is_granted('ROLE_USER')")
 */
class MessagingController extends AbstractController
{
    /**
     * @Route("/api/messaging", name="api_messaging")
     */
    public function index()
    {
        return $this->render('api/messaging/index.html.twig', [
            'controller_name' => 'MessagingController',
        ]);
    }

    /**
     * @Route("/previous/{id}/{offset?}", name="previous", options={"expose"=true})
     * @param $id
     * @param int $offset
     * @param ConversationRepository $conversationRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function previousMessages($id, $offset = 20, ConversationRepository $conversationRepository)
    {
        // TODO: load the previous conversation
        $messagesList = $conversationRepository->findPreviousMessageByConversationIdWithOffset($id, $offset);
        $messages = [];
        foreach ($messagesList as $item) {
            $messages[] = [
                'username' => $item['senderUsername'],
                'avatar' => $item['senderAvatar'],
                'content' => $item['message'],
                'mine' => $this->isCurrentUserSender($item),
            ];
        }
        return $this->json([
            array_reverse($messages),
        ]);
    }

    /**
     * Check whether the current user is the sender of a given message
     * @param array $message
     * @return bool
     */
    private function isCurrentUserSender(array $message)
    {
        return (int)$message['sender_id'] === $this->getUser()->getId();
    }
}
