<?php

    namespace App\Services;

    use App\Entity\User;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\ContainerInterface;
    use Psr\Container\NotFoundExceptionInterface;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class AvatarManager
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
            $this->container = $container;
            $this->fileManager = $fileManager;
        }

        /**
         * Upload the given file and return the full path without the project_dir
         * @param UploadedFile $file
         * @param string|null $oldFilename
         * @return string
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         * @throws \Exception
         */
        public function uploadAvatar(UploadedFile $file, string $oldFilename = null)
        {
            $this->fileManager->setUploadsDirectory(
                $this->getAvatarsUploadDirectory()
            );

            /**
             * filename with the extension.
             */
            $filename = $this->fileManager->uploadFile($file);

            // remove the old file if exists
            if (!is_null($oldFilename)) {
                $this->removeAvatar($oldFilename);
            }

            // return the full path with the filename
            return $this->getAvatarsUploadDirectoryWithoutRoot() . '/' . $filename;
        }

        public function removeAvatar(string $filename)
        {
            $this->fileManager->removeFile($filename);
        }

        /**
         * @return mixed
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         */
        private function getAvatarsUploadDirectory()
        {
            return $this->container->getParameter('avatars_dir');
        }

        private function getAvatarsUploadDirectoryWithoutRoot()
        {
            try {
                return $this->container->getParameter('avatars_dir_no_root');
            } catch (NotFoundExceptionInterface $e) {
            } catch (ContainerExceptionInterface $e) {
            }
        }

        private function generateUniqueName()
        {
            return md5(uniqid());
        }
    }