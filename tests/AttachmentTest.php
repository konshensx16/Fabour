<?php

namespace App\Tests;

use App\Services\AttachmentManager;
use App\Services\FileManager;
use App\Services\ImageManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentTest extends WebTestCase
{
    /** @var ContainerInterface $myContainer */
    private $myContainer;
    /** @var FileManager $fileManager */
    private $fileManager;
    /** @var ImageManager $imageManager */
    private $imageManager;
    /** @var AttachmentManager $attachmentManager */
    private $attachmentManager;
    /** @var string $uploadDirectory */
    private $uploadDirectory;

    private $file;
    /** @var UploadedFile $image */
    private $image;

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->myContainer = $kernel->getContainer();

        $this->fileManager = new FileManager($this->myContainer);
        $this->imageManager = new ImageManager($this->fileManager);

        $this->attachmentManager = new AttachmentManager($this->myContainer, $this->fileManager, $this->imageManager);

        $this->file = tempnam(sys_get_temp_dir(), 'upl');
        imagepng(imagecreatetruecolor(10, 10), $this->file);
        $this->image = new UploadedFile(
            $this->file,
            'image.png'
        );
    }

    public function testUploadAnAttachment()
    {
        $result = $this->attachmentManager->uploadAttachment($this->image);
        $this->assertNotNull($result['filename']);
        $this->assertNotNull($result['path']);

        $this->assertTrue(file_exists($this->myContainer->getParameter('attachments_dir') . '/' . $result['filename']));
    }

    // TODO: test the remove function ?!
    protected function tearDown()
    {
        array_map('unlink', glob($this->myContainer->getParameter('attachments_dir') . "/*.*"));
    }

}