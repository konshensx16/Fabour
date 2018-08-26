<?php 
namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class AcmeTopic implements TopicInterface {

	public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
	{
		// send the message to all subscribers of this topic
		$topic->broadcast([
			'msg' => $connection->resourceId . " has joined " . $topic->getId()
		]);
	}

	// recieve a disconnect 
	public function onUnsubscribe (ConnectionInterface $connection, Topic $topic, WampRequest $request)
	{
		$topic->broadcast([
			'msg' => $connection->resourceId . " has left " . $topic->getId()
		]);
	}

	// recieve publish request for this topic 
	public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
	{
		$topic->broadcast([
			'msg' => $event
		]);
	}

	// like RPC (Remote Procedure Call) will use to prefix the channel
	public function getName()
	{
		return 'acme.topic';
	}
}