<?php

namespace Siestacat\UploadChunkBundle;

use Siestacat\UploadChunkBundle\DependencyInjection\UploadChunkConfiguration;
use Siestacat\UploadChunkBundle\DependencyInjection\UploadChunkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class UploadChunkBundle extends AbstractBundle
{
    public function getContainerExtension():ExtensionInterface
    {
        $this->extension = $this->extension === null ? new UploadChunkExtension : $this->extension;

        return $this->extension;
    }
}