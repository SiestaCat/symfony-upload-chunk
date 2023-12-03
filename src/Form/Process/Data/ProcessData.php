<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Form\Process\Data;

use Siestacat\UploadChunkBundle\Validator as AcmeAssert;

#[AcmeAssert\Process]
class ProcessData
{
    public string $request_id;

    public string $file_id;
}
