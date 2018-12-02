<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/attachment", name="api.attachment.")
 * Class AttachmentController
 * @package App\Controller\Api
 */
class AttachmentController extends AbstractController
{
    /**
     * @Route("/postimage", name="postimage", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postImage(Request $request)
    {
        dump($request->files);

        return $this->json([
            'location' => 'path/to/the/image.jpg'
        ]);
    }
}
