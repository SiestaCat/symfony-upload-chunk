<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process\Data;

use Siestacat\UploadChunkBundle\Document\Request;
use Siestacat\UploadChunkBundle\Service\Process\ProcessRequest;
use Symfony\Component\Form\FormInterface;

class ProcessRequestInstance
{

    public ?string $request_id = null;

    public int $status = ProcessRequest::POST_PROCESS_HAS_PENDING_FILES;

    public ?int $pending_count = null;

    public function __construct(public FormInterface $form)
    {
        $this->request_id = $this->form->isSubmitted() && $this->form->isValid() && $this->form->get('request_id') ? $this->form->get('request_id')->getData() : null;
    }
    
}
