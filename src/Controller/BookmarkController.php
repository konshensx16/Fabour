<?php

namespace App\Controller;

use App\Repository\BookmarkRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Route("/bookmark", name="bookmark.")
 * @Security("is_granted('ROLE_USER')")
 * Class BookmarkController
 * @package App\Controller
 */
class BookmarkController extends AbstractController
{

    /**
     * @Route("/bookmarks", name="bookmarks")
     * @param BookmarkRepository $bookmarkRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bookmarks(BookmarkRepository $bookmarkRepository)
    {
        $currentUser = $this->getUser();
        $bookmarks = $bookmarkRepository->findBookmarksByUserId($currentUser->getId());
        dump($bookmarks);
        return $this->render('post/bookmarks.html.twig', [
            'bookmarks' => $bookmarks
        ]);
    }
}
