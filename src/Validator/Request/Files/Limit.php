<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator\Request\Files;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Limit extends Constraint
{
    public $message = 'error.max_files';

    public $message_no_files = 'error.no_files';
}