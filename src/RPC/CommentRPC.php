<?php
namespace App\RPC;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Ratchet\ConnectionInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Ratchet\Wamp\Topic;


// TODO: create the service for this RPC
class CommentRPC implements RPCInterface, PushableTopicInterface
{
    /**
     * @var ClientManipulatorInterface
     */
    private $clientManipulator;

    public function __construct(ClientManipulatorInterface $clientManipulator)
    {
//        $this->objectManager = $objectManager; // i don't think i need this!
//        $this->formManager = $formManager; // and even this!
        $this->clientManipulator = $clientManipulator;
    }

    public function notifyPublisher(ConnectionInterface $connection, WampRequest $request, $param)
    {
        // notify the user of the new published comment
        // i think i just want the user to be redirected to the Post and then add some sort
        //      of a hash tag to the URL so the page scroll to the comment in question!
        $currentUser = $this->clientManipulator->getClient($connection);
    }

    public function getName()
    {
        return "comment_manager.rpc";
    }

    /**
     * @param Topic $topic
     * @param WampRequest $request
     * @param string|array $data
     * @param string $provider
     * @return string
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        return 'This was called from the controller';
    }
}