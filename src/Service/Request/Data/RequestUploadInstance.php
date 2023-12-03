<?php

namespace Siestacat\UploadChunkBundle\Service\Request\Data;

use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFile;
use Symfony\Component\Form\FormInterface;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequest;

class RequestUploadInstance
{
    /**
     * 
     * @var UploadChunkRequestFile[]
     */
    public array $files = [];

    /**
     * 
     * @var \stdClass[]
     */
    public array $response_parts = [];

    public function __construct(public FormInterface $form, public UploadChunkRequest $request_document)
    {
        if(!$this->form->isSubmitted()) $this->form->submit([]);

        $files_form = $this->form->get('files')->getData();

        $this->files = is_array($files_form) ? $files_form : [];
    }
    
}
