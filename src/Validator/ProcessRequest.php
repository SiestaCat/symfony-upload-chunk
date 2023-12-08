<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
#[\Attribute]
class ProcessRequest extends Constraint
{

    public $message = 'error.csrf';

    public function validatedBy() {
        return ProcessRequestValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}