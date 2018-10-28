<?php
namespace App\Services;

use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    /**
     * @var ContainerInterface $_container
     */
    private $_container;

    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    public function uploadFile(UploadedFile $uploadedFile)
    {
        // check if the file is legit
        if ($uploadedFile instanceof UploadedFile)
        {
            // upload the file
            $filename = $this->generateUniqueName() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $this->getTargetDirectory(),
                $filename
            );
            return $filename;
        }
    }

    public function removeFile($filename)
    {
        if (!empty($filename))
        {
            $filesystem = new Filesystem();
            $filesystem->remove(
                $this->getTargetDirectory() . '/' . $filename
            );
        }

    }

    public function getTargetDirectory()
    {
        return $this->_container->getParameter('avatars_dir');
    }

    /**
     * Generate a unique name
     * @return string
     */
    private function generateUniqueName()
    {
        return md5(uniqid());
    }

}