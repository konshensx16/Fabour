<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
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
     * @Route("/single/{slug}", name="category")
     * @param Category $category
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function category(Category $category, PostRepository $postRepository)
    {
        // TODO: get popular posts based on views
        $popularPosts = $postRepository->findPopularPostsByCategoryWithLimit($category->getId(), 5);
        // TODO: get recently published posts in this category
        $recentPosts = $postRepository->findRecentPostsWithCategory($category->getId());


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
