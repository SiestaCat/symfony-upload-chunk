<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Request\Data;

use Siestacat\UploadChunkBundle\Document\File;
use Symfony\Component\Form\FormInterface;
use Siestacat\UploadChunkBundle\Document\Request;

class RequestUploadInstance
{
    /**
     * 
     * @var File[]
     */
    public array $files = [];

    /**
     * 
     * @var \stdClass[]
     */
    public array $response_parts = [];

    public function __construct(public FormInterface $form, public Request $request_document)
    {
        if(!$this->form->isSubmitted()) $this->form->submit([]);

        $files_form = $this->form->get('files')->getData();

        $this->files = is_array($files_form) ? $files_form : [];
    }
    
}
