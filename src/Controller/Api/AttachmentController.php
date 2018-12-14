<?php

namespace App\Controller\Api;

use App\Entity\Attachment;
use App\Entity\Post;
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
     * @Route("/postimage/{id}", name="postimage", options={"expose"=true})
     *
     * @param Request $request
     * @param Post $post I might want to check if the post actually exists, but that something for the future
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function postImage(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        $file = $request->files->get('file');

        $filenameAndPath = $this->attachmentManager->uploadAttachment($file);

        $attachment = new Attachment();
        $attachment->setFilename($filenameAndPath['filename']);
        $attachment->setPath($filenameAndPath['path']);
        $attachment->setCreatedAt(new \DateTime());

        $attachment->setPost($post);
        $post->addAttachment($attachment);

        $em->persist($attachment);
        $em->flush();

        $em->getConnection()->commit();

        return $this->json([
            'location' => $filenameAndPath['path']
        ]);
    }
}
