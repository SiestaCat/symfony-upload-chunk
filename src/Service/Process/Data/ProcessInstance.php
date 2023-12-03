<?php

namespace Siestacat\UploadChunkBundle\Service\Process\Data;

use Siestacat\UploadChunkBundle\Document\UploadChunkRequest;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFile;
use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessData;
use Symfony\Component\Form\FormInterface;

class ProcessInstance
{
    public ?ProcessData $data = null;

    public ?UploadChunkRequestFile $file = null;

    public ?UploadChunkRequest $request = null;

    public function __construct(public FormInterface $form)
    {
        $this->data = $this->form->getData();
    }
    
}
