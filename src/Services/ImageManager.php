<?php

namespace App\Services;

use Imagine\Gd\Imagine;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    // NOTE: this value was decided from the template used to display the posts
    //       Might changes later if the value isn't correct or the template changed
    const MAX_WIDTH = 1058;
    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {

        $this->fileManager = $fileManager;
    }

    /**
     * Optimize an image by reducing the size and saving it as a jpeg
     * Checks the width and if greater than the max value, the set the width to the max value
     * @param UploadedFile $file
     * @param string $uploadsDirectory
     */
    public function optimizeByResizing(UploadedFile $file, string $uploadsDirectory)
    {
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
                $uploadsDirectory . '/' . $filename
            );

        return $filename;
    }

}