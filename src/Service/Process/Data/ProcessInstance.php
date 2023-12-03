<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process\Data;

use Siestacat\UploadChunkBundle\Document\Request;
use Siestacat\UploadChunkBundle\Document\File;
use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessData;
use Symfony\Component\Form\FormInterface;

class ProcessInstance
{
    public ?ProcessData $data = null;

    public ?File $file = null;

    public ?Request $request = null;

    public function __construct(public FormInterface $form)
    {
        $this->data = $this->form->getData();
    }
    
}
