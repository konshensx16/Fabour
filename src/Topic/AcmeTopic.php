<?php 
namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulator;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AcmeTopic implements TopicInterface, PushableTopicInterface {

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
			'msg' => $connection->resourceId . " has joined " . $topic->getId()
		]);
	}

	// recieve a disconnect 
	public function onUnsubscribe (ConnectionInterface $connection, Topic $topic, WampRequest $request)
	{
		// remove the connection when not subscribed anymore
		// otherwise the counter will always go up
		$this->clients->detach($connection);
		$topic->broadcast([
			'msg' => $connection->resourceId . " has left " . $topic->getId()
		]);
	}

	// recieve publish request for this topic 
	// this looks like the place where to send to count of connected clients
	public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
	{
		$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];

		$serializer = new Serializer($normalizers, $encoders);

		$serializedData = $serializer->serialize($request, 'json');

		$topic->broadcast([
			'msg' => $event,
			'connectedClients' => $topic->count()
		]);
	}

	// like RPC (Remote Procedure Call) will use to prefix the channel
	public function getName()
	{
		return 'acme.topic';
	}

    /**
     * @param Topic $topic
     * @param WampRequest $request
     * @param array|string $data
     * @param string $provider | $provider == amqp (in my case of course)
     */
	public function onPush(Topic $topic, WampRequest $request, $data, $provider)
   {
       // TODO: IF THE CURRENT USER COMMENT OM HIS POST NOTHING SHOULD HAPPEN!
       $user = $this->clientManipulator->findByUsername($topic, $data['author']);
       $theUser = $user['connection']->WAMP->sessionId;
       // get the user using the username
       /** @var UserInterface $user */
       dump($theUser);
        // i think this is never executing
        $topic->broadcast($data['message'], [], [$theUser]);
    }
}