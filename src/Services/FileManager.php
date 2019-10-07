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

    /**
     * @var String
     */
    private $uploadsDirectory;

    /**
     * FileManager constructor.
     * @param ContainerInterface $container
     * @param string $uploadsDirectory
     * @throws \Exception
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * MUST BE CALLED BEFORE ANYTHING ELSE
     * @param string $uploadsDirectory
     * @throws \Exception
     */
    public function setUploadsDirectory(string $uploadsDirectory)
    {
        if (is_null($uploadsDirectory) || $uploadsDirectory === '') {
            throw new \Exception("The uploads directory cannot be null or empty");
        }
        $this->uploadsDirectory = $uploadsDirectory;
    }

    /**
     * Return the filename of the uploaded file with the extension
     * @param UploadedFile $uploadedFile
     * @return string
     */
    public function uploadFile(UploadedFile $uploadedFile)
    {
        // check if the file is legit
        if ($uploadedFile instanceof UploadedFile)
        {
            // upload the file
            $filename = $this->generateUniqueName() . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move(
                $this->uploadsDirectory,
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
                $this->uploadsDirectory . '/' . $filename
            );
        }
    }

    /**
     * Generate a unique name
     * @return string
     */
    public function generateUniqueName()
    {
        return md5(uniqid());
    }

}