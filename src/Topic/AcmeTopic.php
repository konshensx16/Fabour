<?php 
namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AcmeTopic implements TopicInterface {

	private $clients;

	public function __construct()
	{
		$this->clients = new \SplObjectStorage();
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
}