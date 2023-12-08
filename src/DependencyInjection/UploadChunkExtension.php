<?php declare( strict_types = 1 );

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

        $configs = $container->getExtensionConfig('doctrine_mongodb');

        $doctrine_mongodb_config = $configs[0];

        $doctrine_mongodb_config['document_managers']['default']['mappings']['UploadChunkBundle'] = [
            'is_bundle' => true,
            'prefix' => 'Siestacat\UploadChunkBundle\Document',
            'alias' => 'UploadChunkBundle'
        ];

        $container->prependExtensionConfig('doctrine_mongodb', $doctrine_mongodb_config);
    }

    public function getAlias():string
    {
        return 'upload_chunk';
    }
}