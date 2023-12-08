<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process\Data;

use Siestacat\UploadChunkBundle\Document\Request;
use Siestacat\UploadChunkBundle\Form\ProcessRequest\Data\ProcessRequestData;
use Siestacat\UploadChunkBundle\Service\Process\ProcessRequest;
use Symfony\Component\Form\FormInterface;

class ProcessRequestInstance
{

    public ?ProcessRequestData $data = null;

    public ?Request $request = null;

    public int $status = ProcessRequest::POST_PROCESS_HAS_PENDING_FILES;

    public ?int $pending_count = null;

    public function __construct(public FormInterface $form)
    {
        $this->data = $this->form->getData();
    }
    
}
