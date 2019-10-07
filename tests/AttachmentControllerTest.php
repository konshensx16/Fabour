<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentControllerTest extends WebTestCase
{

    protected function setUp()
    {
        // TODO: maybe create a folder or something and remove on teardown

    }

    public function testUploadAttachmentAndAttachItToPost()
    {
        // TODO: Implement this function
        $photo = new UploadedFile('/path/to/photo.jpg', 'photo.jpg', 'image/jpeg', 123);

        $client = $this->createClient();
        $result = $client->request('POST', '/postimage/7D', array('name' => 'Fabien'), array('file' => $photo));
        // assert the file is in there
        $this->assertEquals(1, $client->getRequest()->files->count());
        // TODO: assert the file was uploaded and set

    }

}
