<?php
namespace VKR\S3UploaderBundle\Decorators;

use Aws\S3\S3Client;

class S3ClientDecorator
{
    public function factory($config = [])
    {
        return S3Client::factory($config);
    }
}
