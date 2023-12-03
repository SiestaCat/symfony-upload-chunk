<?php

namespace Siestacat\UploadChunkBundle\Service\Upload\Data;

use Symfony\Component\Form\FormInterface;
use Siestacat\UploadChunkBundle\Form\Upload\Data\UploadPartData;

class UploadPartInstance
{
    public ?UploadPartData $data = null;

    public function __construct(public FormInterface $form)
    {
        $this->data = $this->form->getData();
    }
    
}
