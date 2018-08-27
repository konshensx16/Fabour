<?php 
namespace App\Listeners;

use Gos\Bundle\WebSocketBundle\Event\ClientEvent;
use Gos\Bundle\WebSocketBundle\Event\ClientErrorEvent;
use Gos\Bundle\WebSocketBundle\Event\ServerEvent;
use Gos\Bundle\WebSocketBundle\Event\ClientRejectedEvent;

class AcmeClientEventListener
{

	// called when a client connect
	public function onClientConnect(ClientEvent $event)
	{
		$connection = $event->getConnection();

		echo $connection->resourceId . " has just connected" . PHP_EOL;
	}

	// called when client disconnects
	public function onClientDisconnect(ClientEvent $event)
	{
		$connection = $event->getConnection();

		echo $connection->resourceId . " has just disconnected" . PHP_EOL;
	}

	// called on a client error
	public function onClientError(ClientErrorEvent $event)
	{
		$connection = $event->getConnection();
		$e = $event->getException();

		echo "connection error occured: " . $e->getMessage() . PHP_EOL;
	}

	// called when client rejected by application
	public function onClientRejected(ClientRejectedEvent $event)
	{
		$origin = $event->getOrigin();

		echo "Connection rejected from " . $origin . PHP_EOL;
	}

	// called when server starts
	public function onServerStart(ServerEvent $event)
	{
		$event = $event->getEventLoop();

		echo "Server was sucessfully started!" . PHP_EOL;
	}
}