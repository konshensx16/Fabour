<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\UserRelationship;
use App\Repository\ConversationRepository;
use App\Repository\PostRepository;
use App\Repository\UserRelationshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\DataCollector\PusherDecorator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Form\MessageType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Services\UserManager;


class HomeController extends AbstractController
{
    /**
     * @var PusherDecorator
     */
    private $pusher;

    /**
     * @var Packages
     */
    private $packages;

    private $userManager;

    public function __construct(PusherDecorator $pusher, Packages $packages, UserManager $userManager)
    {
        $this->userManager = $userManager;
        $this->pusher = $pusher;
        $this->packages = $packages;
    }

    /**
     * @Route("/", name="home.index", options={"expose"=true})
     * Probably won't need the request, just throwing it in there
     * @param Request $request
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, PostRepository $postRepository)
    {
        dump($postRepository->findPostsWithCategory());
        return $this->render('home/index.html.twig', [
            'post' => $postRepository->findPostsWithCategory()
        ]);
    }

    /**
     * TODO: i think this is disposable
     * @Route("/conversation/{id?}", name="conversation")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function conversation(Request $request, $id)
    {
        $form = $this->createFormBuilder()
            ->add('message', TextType::class, [
                'attr' => [
                    'placeholder' => 'Press return to send the message...',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-info pull-right'
                ]
            ])
            ->getForm();


//        dump($security->getUser()); this is working fine when in the controller

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // XXX: handle the request
            $em = $this->getDoctrine()->getManager();
        }

        return $this->render('home/conversation.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/testType", name="testing_type")
     */

    public function testType(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        // trying to deserialize the string above
        $messageObject = new Message();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        // don't need to save this in a variable since using the object_to_populate option
        // $serializer->deserialize($decodedData, Message::class, 'json', ['object_to_populate' => $message]);


        // if ($form->isSubmitted())
        // {
        //     dump($request);
        // }

        return $this->render('home/test.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param UserRelationshipRepository $userRelationshipRepository
     * @param ConversationRepository $conversationRepository
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function renderIcons(UserRelationshipRepository $userRelationshipRepository, ConversationRepository $conversationRepository)
    {
        $this->checkIfUserLoggedIn();
        // check if the user logged in
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $result = $userRelationshipRepository->findUsersWithTypePending($currentUser->getId());
        $messagesCount = $conversationRepository->getUnreadMessagesCount($currentUser->getId());
        return $this->render('home/icons.html.twig', [
            'pending' => (bool)$result,
            'messages' => (bool)$messagesCount
        ]);
    }

    /**
     * @Route("/testcascade", name="testCascade")
     * @param UserRelationshipRepository $userRelationshipRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function testCascade(UserRelationshipRepository $userRelationshipRepository)
    {
        $currentUser = $this->getUser();
        $friends = $userRelationshipRepository->findUserFriendsById($currentUser->getId());

        $friendsNames = [];
        /** @var UserRelationship $friend */
        foreach ($friends as $friend) {
            $friendsNames[] = $friend->getRelatedUser()->getUsername();
        }

        // send the notification
        try {
            $this->pusher->push([
                'action' => 'wants to add you as a friend',
                'avatar' => $this->userManager->getUserAvatar($currentUser->getAvatar()),
                'notifier' => 'admin',
                'url' => $this->generateUrl('friends.pending'),
                'username' => $currentUser->getUsername(),
            ], 'notification_topic');
        } catch (\Exception $e) {
            $e->getTrace();
        }

        return $this->json(
            [
                'type' => 'success',
                'avatar' => $this->userManager->getUserAvatar($currentUser->getAvatar())
            ]
        );
    }

    /*
     * @Route("/testPusher", name="testPusher")
     */
    public function testPusher(UserRelationshipRepository $userRelationshipRepository)
    {
        $currentUser = $this->getUser();
        $friends = $userRelationshipRepository->findUserFriendsById($currentUser->getId());

        $friendsNames = [];
        /** @var UserRelationship $friend */
        foreach ($friends as $friend) {
            $friendsNames[] = $friend->getRelatedUser()->getUsername();
        }

        // send the notification
        try {
            $this->pusher->push([
                'username' => $currentUser->getUsername(),
                'action' => 'just published a new post',
                'notifiers' => $friendsNames,
                'avatar' => $this->packages->getUrl('assets/img/') . $currentUser->getAvatar(),
                'url' => $this->generateUrl('post.display', ['id' => 5]),
            ], 'notification_topic');
        } catch (\Exception $e) {
            $e->getTrace();
        }

        return $this->json([
            'data' => 'success',
        ]);
    }

    /**
     * @Route("/pusher")
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pusherTester(UserRepository $userRepository)
    {
        $currentUser = $this->getUser();

        $users = $userRepository->findAll();

        $friendsNames = [];

        foreach ($users as $user)
        {
            $friendsNames[] = $user->getUsername();
        }
        try {
            // DO this a 100 times and see what's the result
            // increase the value to 1,000, then 100,000, then a 1,000,000 if possible.
            // NOTE: basically nothing happened at 100, message rate peeked at 20s and that's it
            for ($i = 0; $i < 10000; $i++) {
                $this->pusher->push([
                    'username' => $currentUser->getUsername(),
                    'action' => 'just published a new post',
                    'notifiers' => $currentUser->getUsername(),
                    'avatar' => $currentUser->getAvatar(),
                    'url' => $this->generateUrl('post.display', ['id' => 5]),
                ], 'notification_topic');
            }
        } catch (\Exception $e) {
            $e->getTrace();
        }

        return $this->json(['data' => 'success?',
        ]);
    }


    /**
     * Check if the user is logged in, if not will redirect him to the login page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function checkIfUserLoggedIn()
    {
        if (!$this->getUser() instanceof UserInterface) {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    /**
     * Return the correct full url of the user avatar
     * @param  string $avatar [description]
     * @return [type] [description]
     */
    private function getUserAvatar(string $avatar)
    {
        if ($avatar === 'avatar.png')
        {
            return $this->packages->getUrl('assets/img/') . $avatar;
        }
        else
        {
            return $this->packages->getUrl('uploads/avatars/') . $avatar;
        }
    }
}
