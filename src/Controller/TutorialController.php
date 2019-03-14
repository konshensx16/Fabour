<?php

namespace App\Controller;

use App\Entity\Tutorial;
use App\Form\TutorialType;
use App\Repository\PostRepository;
use App\Repository\TutorialRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tutorial", name="tutorial.")
 */
class TutorialController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PostRepository $postRepository)
    {
        $tutorial = new Tutorial();
        $form = $this->createForm(TutorialType::class, $tutorial);

//        $tutorial->setTitle('Hello ttke');
//        $tutorial->setContent('This is the content of the tutorial instance');

        
        return $this->render('tutorial/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/find")
     *
     * @param TutorialRepository $tutorialRepository
     * @return void
     */
    public function findByCategory(TutorialRepository $tutorialRepository) {
        $em = $this->getDoctrine()->getManager();

        $product = new Tutorial();

        $product->setTitle('new title');
        $product->setContent('new content');

        $product->setCategories(['men', 'jeans']);

        $em->persist($product);
        $em->flush();

        $data = $tutorialRepository->findAll();

        dump($data); die;
        
    }
}
