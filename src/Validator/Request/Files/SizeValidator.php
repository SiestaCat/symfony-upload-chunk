<?php

namespace Siestacat\UploadChunkBundle\Validator\Request\Files;

use Siestacat\BytesToHumanReadable\BytesToHumanReadable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Siestacat\UploadChunkBundle\Document\File;

#[\Attribute]
class SizeValidator extends ConstraintValidator
{

    public function __construct(private int $upload_max_bytes){}

    /**
     * @param File[] $files 
     */
    public function validate(mixed $files, Constraint $constraint):void
    {
        if(!is_array($files)) throw new \Exception('$files argument must be array');

        if (!$constraint instanceof Size) {
            throw new UnexpectedTypeException($constraint, Size::class);
        }

        $total_bytes = 0;

        foreach($files as $file)
        {
            $total_bytes += $file->size;

            if($total_bytes > $this->upload_max_bytes)
            {
                $this->context->buildViolation($constraint->message)
                ->setParameter('{size}', BytesToHumanReadable::convert($this->upload_max_bytes))
                ->setTranslationDomain('upload')
                ->addViolation();
            }
        }
    }
    
}