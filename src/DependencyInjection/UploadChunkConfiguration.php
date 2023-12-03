<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class UploadChunkConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('upload_chunk');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->integerNode('max_files')->defaultValue(100)->end()
                    ->integerNode('max_bytes')->defaultValue(100)->end()
                    ->scalarNode('tmp_path')->end()
                ->end()
        ;

        return $treeBuilder;
    }
}