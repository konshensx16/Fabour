<?php 
namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulator;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class FriendshipTopic implements TopicInterface, PushableTopicInterface {

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
	}

	// recieve a disconnect 
	public function onUnsubscribe (ConnectionInterface $connection, Topic $topic, WampRequest $request)
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
		return 'friendship.topic';
	}

    /**
     * @param Topic $topic
     * @param WampRequest $request
     * @param array|string $data
     * @param string $provider | $provider == amqp (in my case of course)
     */
	public function onPush(Topic $topic, WampRequest $request, $data, $provider)
   {
       // TODO: this only send to a single user, what if we had multiple users (like replies) ??
       // this should not failed and the user should never be null!
       // get the user using the username
       $user = $this->clientManipulator->findByUsername($topic, $data['send_to_user']);
       $theUser = $user['connection']->WAMP->sessionId;
       $topic->broadcast([$data['message'], $data['type']], [], [$theUser]);
    }
}