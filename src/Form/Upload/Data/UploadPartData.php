<?php

namespace Siestacat\UploadChunkBundle\Form\Upload\Data;

use Siestacat\UploadChunkBundle\Validator as AcmeAssert;
use Symfony\Component\HttpFoundation\File\File;

#[AcmeAssert\UploadPart]
class UploadPartData
{
    public string $request_id;

    public string $file_id;

    public int $part_index;

    public File $file;
}
