<?php

namespace App\Controller;

use App\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Form\MessageType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     * Probably won't need the request, just thorwing it in there
     */
    public function index(Request $request)
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository(Item::class)->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
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
}
