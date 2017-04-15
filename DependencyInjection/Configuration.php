<?php
namespace VKR\S3UploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vkr_s3_uploader');
        /** @noinspection PhpUndefinedMethodInspection */
        $rootNode
            ->children()
                ->scalarNode('uploader_service')->defaultValue('vkr_s3_uploader.s3_uploader')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
