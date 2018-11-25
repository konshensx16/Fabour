<?php

    namespace App\Controller\Api;

    use App\Repository\PostRepository;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpKernel\Event\PostResponseEvent;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/api/post", name="api.posts.")
     * Class PostController
     * @package App\Controller\Api
     */
    class PostController extends AbstractController
    {

        /**
         * NOTE: this will get 20 posts for a given user, if no username is provided then the current user's
         *         username will be used
         * TODO: this will need to change if i need other urls in the route
         * @Route("/posts/{username?}", name="getPosts", options={"expose"=true})
         * @param $username
         * @param PostRepository $postRepository
         * @return \Symfony\Component\HttpFoundation\JsonResponse
         * @throws \Doctrine\ORM\NonUniqueResultException
         */
        public function getPosts($username, PostRepository $postRepository)
        {
            if (!is_null($username)) {
                $posts = $postRepository->findRecentlyPublishedPostsByUsernameWithLimit($username);
                $total = $postRepository->getTotalPostsByUsername($username);
            } else {
                $posts = $postRepository->findRecentlyPublishedPostsByWithLimit($this->getUser()->getUsername());
                $total = $postRepository->getTotalPosts();
            }
            // format the created_at string so i can pass it directly
            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i]['created_at'] = ($posts[$i]['created_at'])->format('Y M d');
            }
            return $this->json([
                'posts' => $posts,
                'total' => $total
            ]);
        }

        /**
         * @Route("/more/{offset}/{username?}", name="getMorePosts", options={"expose"=true})
         * @param $username
         * @param $offset
         * @param PostRepository $postRepository
         * @return \Symfony\Component\HttpFoundation\JsonResponse
         */
        public function getMorePosts($username, $offset, PostRepository $postRepository)
        {
            $posts = [];
            if (!is_null($username)) {
                $posts = $postRepository->findRecentlyPublishedPostsByUsernameWithLimit($username,
                    10,
                    $offset
                );
            } else {
                $posts = $postRepository->findRecentlyPublishedPostsByWithLimit($this->getUser()->getUsername(),
                    10,
                    $offset
                );
            }
            // format the created_at string so i can pass it directly
            for ($i = 0; $i < count($posts); $i++) {
                $posts[$i]['created_at'] = ($posts[$i]['created_at'])->format('Y M d');
            }
            return $this->json($posts);
        }


    }
