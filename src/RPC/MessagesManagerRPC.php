<?php 
namespace App\RPC;

use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Ratchet\ConnectionInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use App\Form\MessageType;
use App\Entity\Message;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Persistence\ObjectManager;

class MessagesManagerRPC implements RPCInterface
{
	private $formManager;
	private $objectManager;

	public function __construct(FormFactoryInterface $formManager, ObjectManager $objectManager)
	{
		$this->formManager = $formManager;
		$this->objectManager = $objectManager;
	}

    /**
     * This is my custom function that will be called from the client-side
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param $params
     * @return Message
     */
	public function storeMessage(ConnectionInterface $connection, WampRequest $request, $params)
	{
		// get the params
		// im expecting a serialized form
		// i might need to get the form here, note sure
		$decodedData = json_decode($params['form']);
		
		// create a form and handle the request maybe ??
		$messageObject = new Message();
		// create == createForm()
		$encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizers, $encoders);

		// don't need to save this in a variable since using the object_to_populate option
		$serializer->deserialize($params['form'], Message::class, 'json', ['object_to_populate' => $messageObject]);
		// this is null right now
		// could be something that has to do with the names being prefixed with 'form'
		dump($messageObject);

		// store the object in the database
		$this->objectManager->persist($messageObject);
		$this->objectManager->flush();
		
		// this is returning an empty object, not sure if i need to serialize it too
		return $messageObject; // null means an error 
	}

	public function getName()
	{
		return "messages_manager.rpc";
	}

}