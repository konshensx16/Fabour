<?php

namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulator;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class NotificationTopic implements TopicInterface, PushableTopicInterface
{

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
    }

    // recieve a disconnect
    public function onUnsubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // remove the connection when not subscribed anymore
        // otherwise the counter will always go up
        $this->clients->detach($connection);
    }

    // recieve publish request for this topic
    // this looks like the place where to send to count of connected clients
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $topic->broadcast([
            'msg' => $event,
            'connectedClients' => $topic->count()
        ]);
    }

    // like RPC (Remote Procedure Call) will use to prefix the channel
    public function getName()
    {
        return 'notification.topic';
    }

    /**
     * @param Topic $topic
     * @param WampRequest $request
     * @param array|string $data
     * @param string $provider | $provider == amqp (in my case of course)
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        // TODO: check if the $data[notifiers] is an array
        // create an array containing session ids of people to receive the message
        $eligibleUsers = [];
        if (in_array('notifiers', array_keys($data)) && is_array($data['notifiers'])) {
            dump('notifiers is an array');
            foreach ($data['notifiers'] as $friend) {
                // NOTE: this code will go through all your friends 1M friend = 1M iteration!!
                $user = $this->clientManipulator->findByUsername($topic, $friend);
                dump($friend);
                // check if the user exists in the session (just check if not null)
                if ($user) {
                    $eligibleUsers[] = $user['connection']->WAMP->sessionId;
                }
            }
        }
        else
        {
            dump('notifier is just a string');
            // just a string
            $user = $this->clientManipulator->findByUsername($topic, $data['notifier']);
            $eligibleUsers[] = $user['connection']->WAMP->sessionId;
        }


        $topic->broadcast($data, [], $eligibleUsers);
    }
}