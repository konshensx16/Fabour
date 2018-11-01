<?php

namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulator;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class MessageTopic implements TopicInterface, PushableTopicInterface
{
    /** @var \SplObjectStorage $clients */
    private $clients;

    /** @var ClientManipulator $clientManipulator */
    private $clientManipulator;

    public function __construct(ClientManipulator $clientManipulator)
    {
        $this->clients = new \SplObjectStorage();
        $this->clientManipulator = $clientManipulator;
    }

    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // store the newly connected client
        $this->clients->attach($connection);
        // send the message to all subscribers of this topic
        $topic->broadcast([
            'msg' => 'new client connected',
        ]);
    }

    // recieve a disconnect
    public function onUnsubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // remove the connection when not subscribed anymore
        // otherwise the counter will always go up
        $this->clients->detach($connection);

        $topic->broadcast([
            'msg' => 'client disconnected',
        ]);
    }

    // recieve publish request for this topic
    // this looks like the place where to send to count of connected clients
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        // TODO: get the message and save it to the db
        // TODO: send the message to the other user
        dump('OnPublish trigerred');
        dump($event);
        $topic->broadcast([
            'msg' => $event,
        ]);
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