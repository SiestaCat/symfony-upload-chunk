<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Siestacat\UploadChunkBundle\Form\ProcessRequest\Data\ProcessRequestData;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Service\RequestSession;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProcessRequestValidator extends ConstraintValidator
{

    public function __construct
    (
        private RequestSession $requestSessionService,
        private FileRepository $fileRepository
    ){}

    /**
     * @param ProcessRequestData $data 
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof ProcessRequest) {
            throw new UnexpectedTypeException($constraint, ProcessRequest::class);
        }

        if(!$this->requestSessionService->exists($data->request_id)) return $this->triggerAddViolation($constraint);
    }

    private function triggerAddViolation(Constraint $constraint):void
    {
        $this->context->buildViolation($constraint->message)
        ->setTranslationDomain('upload')
        ->addViolation();
    }

}