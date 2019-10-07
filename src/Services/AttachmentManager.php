<?php

    namespace App\Services;

    use Imagine\Gd\Imagine;
    use Imagine\Image\Box;
    use Imagine\Image\Point;
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
        /**
         * @var ImageManager
         */
        private $imageManager;

        public function __construct(ContainerInterface $container, FileManager $fileManager, ImageManager $imageManager)
        {
            $this->fileManager = $fileManager;
            $this->container = $container;
            $this->imageManager = $imageManager;
        }

        /**
         * @throws \Exception
         */
        private function setUploadDirectory()
        {
            $this->fileManager->setUploadsDirectory(
                $this->getUploadsDirectory()
            );
        }

        /**
         * return the full path for the attachment after uploading
         * @param UploadedFile $file
         * @return array
         * @throws \Exception
         */
        public function uploadAttachment(UploadedFile $file)
        {

            $this->setUploadDirectory();

            $filename = $this->imageManager->optimizeByResizing($file, $this->getUploadsDirectory());

            return [
                'path' => $this->getUploadsDirectoryWithoutRoot() . '/' . $filename,
                'filename' => $filename
            ];
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