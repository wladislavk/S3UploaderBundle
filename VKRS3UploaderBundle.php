<?php
namespace VKR\S3UploaderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use VKR\S3UploaderBundle\DependencyInjection\VKRS3UploaderExtension;

class VKRS3UploaderBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new VKRS3UploaderExtension();
    }
}
