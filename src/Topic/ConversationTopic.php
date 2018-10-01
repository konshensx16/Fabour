<?php
namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ConversationTopic implements TopicInterface {

    private $clients;
    private $clientManipulator;

    /**
     * The Security component doesn't seem to work, trying something from the docs instead
     * ConversationTopic constructor.
     * @param ClientManipulatorInterface $clientManipulator
     */
    public function __construct(ClientManipulatorInterface $clientManipulator)
    {
        $this->clients = new \SplObjectStorage();
        $this->clientManipulator = $clientManipulator;
    }

    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // store the newly connected client
        $this->clients->attach($connection);
        $currentUser = $this->clientManipulator->getClient($connection);
        dump($currentUser);
        // send the message to all subscribers of this topic
        $topic->broadcast([
            'type' => 'user_joined',
            // TODO: include the username
            'user' => $currentUser->getUsername(),
            'msg' => $connection->resourceId . " has joined " . $topic->getId(),
            'connectedClients' => $topic->count()
        ]);
    }

    // recieve a disconnect
    public function onUnsubscribe (ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // remove the connection when not subscribed anymore
        // otherwise the counter will always go up
        $this->clients->detach($connection);
        $topic->broadcast([
            'msg' => $connection->resourceId . " has left " . $topic->getId(),
            'connectedClients' => $topic->count()
        ]);
    }

    // receive publish request for this topic
    // this looks like the place where to send to count of connected clients
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $currentUser = $this->clientManipulator->getClient($connection);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $serializedData = $serializer->serialize($request, 'json');

        $topic->broadcast([
            'type' => 'message', // this could be user_joined or just a message
            'msg' => $event,
            'user' => $currentUser->getUsername(),
            'connectedClients' => $topic->count()
        ]);
    }

    // like RPC (Remote Procedure Call) will use to prefix the channel
    public function getName()
    {
        return 'conversation.topic';
    }
}