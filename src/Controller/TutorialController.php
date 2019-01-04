<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tutorial", name="tutorial")
 **/
class TutorialController extends AbstractController
{

    /**
     * @Route("/post/{id}")
     * @Entity("post", expr="repository.findPublishedById(id)", options={"converter"="custom_conveter"})
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPost(Post $post)
    {
        return $this->render('tutorial/index.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/tester")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tester(Request $request, CategoryRepository $categoryRepository)
    {
        dump($request->getMethod());
        if ($request->getMethod() == 'POST') {
            dump($request->request); die;
        }

        return $this->render('tutorial/remove.html.twig', [
            'categories' => $categoryRepository->findAll()
        ]);
    }



}
