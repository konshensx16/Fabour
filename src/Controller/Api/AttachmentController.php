<?php

namespace App\Controller\Api;

use App\Services\AttachmentManager;
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
     * @var AttachmentManager
     */
    private $attachmentManager;

    public function __construct(AttachmentManager $attachmentManager)
    {
        $this->attachmentManager = $attachmentManager;
    }

    /**
     * @Route("/postimage", name="postimage", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function postImage(Request $request)
    {
        $file = $request->files->get('file');
        $location = $this->attachmentManager->uploadAttachment($file);

        return $this->json([
            'location' => $location
        ]);
    }
}
