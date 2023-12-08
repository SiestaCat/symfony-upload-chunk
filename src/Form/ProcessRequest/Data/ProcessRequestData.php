<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Form\ProcessRequest\Data;

use Siestacat\UploadChunkBundle\Validator as AcmeAssert;

#[AcmeAssert\ProcessRequest()]
class ProcessRequestData
{
    public string $request_id;
}
