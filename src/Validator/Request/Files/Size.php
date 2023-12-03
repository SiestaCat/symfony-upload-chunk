<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator\Request\Files;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Size extends Constraint
{
    public $message = 'error.bytes';
}