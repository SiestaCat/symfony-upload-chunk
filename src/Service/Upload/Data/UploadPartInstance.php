<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Upload\Data;

use Siestacat\UploadChunkBundle\Document\File;
use Symfony\Component\Form\FormInterface;
use Siestacat\UploadChunkBundle\Form\Upload\Data\UploadPartData;

class UploadPartInstance
{
    public ?UploadPartData $data = null;

    public ?File $file = null;

    public bool $is_done = false;

    public function __construct(public FormInterface $form)
    {
        $this->data = $this->form->getData();
    }
    
}
