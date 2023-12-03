<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator\Request\Files;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[\Attribute]
class LimitValidator extends ConstraintValidator
{

    public function __construct(private int $upload_max_files){}

    public function validate(mixed $files, Constraint $constraint):void
    {
        if (!$constraint instanceof Limit) {
            throw new UnexpectedTypeException($constraint, Limit::class);
        }

        if(!is_array($files)) throw new \Exception('$files argument must be array');

        if(count($files) === 0)
        {
            $this->context->buildViolation($constraint->message_no_files)
            ->setTranslationDomain('upload')
            ->addViolation();
        }

        if(count($files) > $this->upload_max_files)
        {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{files}', $this->upload_max_files)
            ->setTranslationDomain('upload')
            ->addViolation();
        }
    }
    
}