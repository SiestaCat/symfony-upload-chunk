<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process\Data;

use Siestacat\UploadChunkBundle\Document\Request;
use Siestacat\UploadChunkBundle\Service\Process\ProcessRequest;

class ProcessRequestInstance
{
    public function __construct
    (
        public string $request_id,
        public int $status = ProcessRequest::POST_PROCESS_HAS_PENDING_FILES,
        public int $pending
    )
    {}
    
}
