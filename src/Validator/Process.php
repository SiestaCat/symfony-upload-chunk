<?php

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
#[\Attribute]
class Process extends Constraint
{

    public $message = 'error.csrf';

    public function validatedBy() {
        return ProcessValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}