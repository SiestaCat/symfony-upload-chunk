<?php

namespace Siestacat\UploadChunkBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Parser as YamlParser;

class UploadChunkExtension extends Extension implements PrependExtensionInterface
{

    const CONFIG_DIR = __DIR__ . '/../../config';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG_DIR));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new UploadChunkConfiguration, $configs);

        $container->setParameter('upload_chunk.max_files', $config['max_files']);
        $container->setParameter('upload_chunk.max_bytes', $config['max_bytes']);
        $container->setParameter('upload_chunk.tmp_path', $config['tmp_path']);

    }

    public function prepend(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG_DIR . '/packages'));

        $this->prependDoctrineMongoDbExtension($container, $loader);
    }

    private function prependDoctrineMongoDbExtension(ContainerBuilder $container, YamlFileLoader $loader):void
    {

        $yamlParser = new YamlParser();

        $config = $yamlParser->parseFile(self::CONFIG_DIR . '/packages/doctrine_mongodb.yaml');

        $container->prependExtensionConfig('doctrine_mongodb', $config['doctrine_mongodb']);
    }

    public function getAlias():string
    {
        return 'upload_chunk';
    }
}