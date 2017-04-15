<?php
namespace VKR\S3UploaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VKRS3UploaderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);
        $container->setParameter('vkr_s3_uploader.uploader_service', $processedConfiguration['uploader_service']);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'vkr_s3_uploader';
    }
}
