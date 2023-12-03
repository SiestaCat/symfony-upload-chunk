<?php

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
#[\Attribute]
class UploadPart extends Constraint
{
    public $message = 'error.csrf';

    public function validatedBy() {
        return UploadPartValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}