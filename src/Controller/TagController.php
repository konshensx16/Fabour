<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/tag", name="tag.")
 */
class TagController extends AbstractController
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }
    
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $this->tagRepository->findAll()
        ]);
    }

    /**
     * @Route("/{slug}", name="single")
     * @param Tag $tag
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSingle(Tag $tag) {

        return $this->render('tag/single.html.twig', [
            'tag' => $tag,
        ]);
    }
}
