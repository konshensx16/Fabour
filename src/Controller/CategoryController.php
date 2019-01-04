<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category", name="category.")
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{
    /**
     * @var ImageManager
     */
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }
    
    /**
     * @Route("/single/{slug}", name="category", options={"expose"=true})
     * @param Category $category
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function category(Category $category, PostRepository $postRepository)
    {
        // get popular posts based on views
        $popularPosts = $postRepository->findPopularPostsByCategoryWithLimit($category->getId(), 5);
        // get recently published posts in this category
        $recentPosts = $postRepository->findRecentPostsWithCategory($category->getId());

        /** @var Post $post */
        foreach ($popularPosts as $post) {
            $post->setContent(strip_tags($post->getContent()));
            $post->setThumbnail($this->imageManager->getThumbnail($post));
        }

        /** @var Post $post */
        foreach ($recentPosts as $post) {
            $post->setContent(strip_tags($post->getContent()));
            $post->setThumbnail($this->imageManager->getThumbnail($post));
        }

        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'category' => $category,
            'popular_posts' => $popularPosts,
            'recentPosts' => $recentPosts
        ]);
    }

    /**
     * @Route("/categories", name="categories")
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allCategories(CategoryRepository $categoryRepository)
    {
        return $this->render('category/all.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

}
