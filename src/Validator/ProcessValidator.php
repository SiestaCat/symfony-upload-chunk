<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessData;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Service\RequestSession;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProcessValidator extends ConstraintValidator
{

    public function __construct
    (
        private RequestSession $requestSessionService,
        private FileRepository $fileRepository
    ){}

    /**
     * @param ProcessData $data 
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof Process) {
            throw new UnexpectedTypeException($constraint, Process::class);
        }

        if
        (
            !$this->requestSessionService->exists($data->request_id) ||
            $this->fileRepository->fetchOne($data->request_id, $data->file_id) === null

        ) return $this->triggerAddViolation($constraint);
    }

    private function triggerAddViolation(Constraint $constraint):void
    {
        $this->context->buildViolation($constraint->message)
        ->setTranslationDomain('upload')
        ->addViolation();
    }

}