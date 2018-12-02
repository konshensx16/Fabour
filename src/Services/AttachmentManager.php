<?php

    namespace App\Services;

    use Psr\Container\ContainerInterface;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class AttachmentManager
    {

        /**
         * @var FileManager
         */
        private $fileManager;
        /**
         * @var ContainerInterface
         */
        private $container;

        public function __construct(ContainerInterface $container, FileManager $fileManager)
        {
            $this->fileManager = $fileManager;
            $this->container = $container;
        }

        /**
         * @throws \Exception
         */
        public function setUploadDirectory()
        {
            $this->fileManager->setUploadsDirectory(
                $this->getUploadsDirectory()
            );
        }

        /**
         * return the full path for the attachment after uploading
         * @param UploadedFile $file
         * @return string
         * @throws \Exception
         */
        public function uploadAttachment(UploadedFile $file)
        {
            $this->setUploadDirectory();
            $filename = $this->fileManager->uploadFile($file);

            return $this->getUploadsDirectoryWithoutRoot() . '/' . $filename;
        }

        /**
         * @param string $filename
         * @throws \Exception
         */
        public function removeAttachment(string $filename)
        {
            $this->setUploadDirectory();
            $this->fileManager->removeFile($filename);
        }

        private function getUploadsDirectory()
        {
            return $this->container->getParameter('attachments_dir');
        }

        private function getUploadsDirectoryWithoutRoot()
        {
            return $this->container->getParameter('attachments_dir_no_root');
        }
    }