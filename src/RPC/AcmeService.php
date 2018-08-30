<?php
namespace App\RPC;

use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Ratchet\ConnectionInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class AcmeService implements RPCInterface
{
	/**
	 * All my other functions can go under this comment
	 */
	// this function will be removed later, this is just for testing purposes
	public function addFunc(ConnectionInterface $connection, WampRequest $request, $params)
	{
		return [
			'result' => array_sum($params)
		];
	}


	/**
	 * required function from the RPCInterface. 
	 */
	public function getName()
	{
		return "acme.rpc";
	}
}