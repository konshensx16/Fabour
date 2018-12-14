<?php

namespace App\Services;

use GuzzleHttp\Psr7\UploadedFile;

class ImageManager
{
    // NOTE: this value was decided from the template used to display the posts
    //       Might changes later if the value isn't correct or the template changed
    const MAX_WIDTH = 1058;

    public function __construct()
    {

    }

    /**
     * Optimize an image by reducing the size and saving it as a jpeg
     * Checks the width and if greater than the max value, the set the width to the max value
     * @param UploadedFile $file
     */
    public function optimize(UploadedFile $file)
    {

    }

}