<?php

namespace Siestacat\UploadChunkBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Siestacat\UploadChunkBundle\Form\Upload\Data\UploadPartData;
use Siestacat\UploadChunkBundle\Repository\FilePartRepository;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Service\RequestSession;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UploadPartValidator extends ConstraintValidator
{

    public function __construct
    (
        private RequestSession $requestSessionService,
        private FileRepository $fileRepository,
        private FilePartRepository $filePartRepository
    ){}

    /**
     * @param UploadPartData $data 
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof UploadPart) {
            throw new UnexpectedTypeException($constraint, UploadPart::class);
        }

        $db_file_part = $this->filePartRepository->fetchOne($data->request_id, $data->file_id, $data->part_index);

        if
        (
            !$this->requestSessionService->exists($data->request_id) ||
            $this->fileRepository->fetchOne($data->request_id, $data->file_id) === null ||
            $db_file_part === null

        ) return $this->triggerAddViolation($constraint);

        if($data->file->getSize() <> $db_file_part->size) return $this->triggerAddViolation($constraint);
    }

    private function triggerAddViolation(Constraint $constraint):void
    {
        $this->context->buildViolation($constraint->message)
        ->setTranslationDomain('upload')
        ->addViolation();
    }

}