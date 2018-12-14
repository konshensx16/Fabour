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
         * @return array
         * @throws \Exception
         */
        public function uploadAttachment(UploadedFile $file)
        {
            $this->setUploadDirectory();
            // TODO: optimize the image
            // if the width is greater than 1058px then make the width 1058px
            $imagine = new Imagine();

            $fileExtension = $file->guessExtension();
            $filename = $this->fileManager->generateUniqueName() . '.' . $fileExtension;
            $image = $imagine->open($file);

            $dimensions = $image->getSize();

            if ($dimensions->getWidth() > ImageManager::MAX_WIDTH)
            {
                $dimensions = $dimensions->widen(ImageManager::MAX_WIDTH);
            }

            $image
                ->crop(
                    new Point(0, 0),
                    $dimensions
                )
                ->save(
                    $this->getUploadsDirectory() . '/' . $filename
                );


            // TODO: this line might get removed since the imagine library will be saving the file
//            $filename = $this->fileManager->uploadFile($file);

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