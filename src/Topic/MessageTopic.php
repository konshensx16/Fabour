<?php

namespace App\Topic;

use App\Services\MessageManager;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulator;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageTopic implements TopicInterface, PushableTopicInterface
{
    /** @var \SplObjectStorage $clients */
    private $clients;

    /** @var ClientManipulator $clientManipulator */
    private $clientManipulator;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var MessageManager
     */
    private $messageManager;

    private $hash;

    public function __construct(ClientManipulator $clientManipulator, Security $security, MessageManager $messageManager)
    {
        $this->hash = md5(uniqid());
        $this->clients = new \SplObjectStorage();
        $this->clientManipulator = $clientManipulator;
        $this->security = $security;
        $this->messageManager = $messageManager;
    }

    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // store the newly connected client
        $this->clients->attach($connection);
        // send the message to all subscribers of this topic
        // TODO: send a signal indicating that the user is online ??
//        $topic->broadcast(
//            'new client connected'
//        );
    }

    // receive a disconnect
    public function onUnsubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // remove the connection when not subscribed anymore
        // otherwise the counter will always go up
        $this->clients->detach($connection);
//
//        $topic->broadcast([
//            'msg' => 'client disconnected',
//        ]);
    }

    // receive publish request for this topic
    // this looks like the place where to send to count of connected clients
    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     * @return bool
     * @throws \Exception
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $currentUser = $this->security->getUser();
        // Check if the user not the same current logged in user
        // NOTE: im not sure how is thing going to work
        $recipient = $event['recipient'];
        $sender= $event['sender'];
        if ($recipient == $sender) {
            throw new \Exception("Something bad happened, check the Message Topic ");
        }

        $conversation_id = $request->getAttributes()->get('id');

        $message = $event['message'];

        // check if the currentUser is logged in
        if (!$currentUser instanceof UserInterface) {
            return false;
        }
        // save the message message before sending it, if it's not saved then don't even send it
        $this->messageManager->saveMessage($message, $recipient, $conversation_id, $sender);
        // send the message to just the other user
        $user = $this->clientManipulator->findByUsername($topic, $recipient);
        if ($user) {
            $topic->broadcast([
                'msg' => $message,
                'avatar' => $event['avatar']
            ], [], [$user['connection']->WAMP->sessionId]);
        }
    }

    // like RPC (Remote Procedure Call) will use to prefix the channel
    public function getName()
    {
        return 'message.topic';
    }

    /**
     * @param Topic $topic
     * @param WampRequest $request
     * @param array|string $data
     * @param string $provider | $provider == amqp (in my case of course)
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        // TODO: all this needs to change!
        // create an array containing session ids of people to receive the message
        $eligibleUsers = [];

        foreach ($data['friends'] as $friend) {
            $user = $this->clientManipulator->findByUsername($topic, $friend);
            $eligibleUsers[] = $user['connection']->WAMP->sessionId;
        }

        $topic->broadcast([$data['message'], $data['type'], $data['url']], [], $eligibleUsers);
    }
}