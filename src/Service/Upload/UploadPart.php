<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Upload;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Form\Upload\UploadPartType;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Service\Upload\Data\UploadPartInstance;
use Siestacat\UploadChunkBundle\Document\FilePart;
use Siestacat\UploadChunkBundle\Repository\FilePartRepository;
use Siestacat\UploadChunkBundle\Repository\RequestRepository;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;
use Symfony\Component\HttpKernel\KernelInterface;
use Siestacat\UploadChunkBundle\Service\RequestSession;

class UploadPart
{

    const EXCEPTION_STOP_CODE = 99;

    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private FilePartRepository $filePartRepository,
        private FileRepository $fileRepository,
        private KernelInterface $kernel,
        private RequestSession $requestSessionService,
        private DestroyRequest $destroyRequestService
    )
    {}

    public function uploadPart():UploadPartInstance
    {
        try
        {
            $instance = new UploadPartInstance
            (
                $this->formFactory->create(UploadPartType::class)->handleRequest($this->requestStack->getCurrentRequest())
            );

            if(!$instance->form->isValid() && $instance->data)
            {
                $this->destroyRequestService->destroy($instance->data->request_id);
            }

            if($instance->form->isValid())
            {
                $part = $this->filePartRepository->fetchOne($instance->data->request_id, $instance->data->file_id, $instance->data->part_index);
                
                if($part->is_done === 0)
                {
                    if(!move_uploaded_file($instance->data->file->getRealPath(), $part->tmp_path))
                    {
                        throw new \Exception('Unable to move uploaded file');
                    }

                    $tmp_path_size = filesize($part->tmp_path);

                    if($tmp_path_size <> $part->size)
                    {
                        throw new \Exception(sprintf('Uploaded part size %d is different than expected %d', $tmp_path_size, $part->size), self::EXCEPTION_STOP_CODE);
                    }

                    $this->filePartRepository->documentManager->createQueryBuilder(FilePart::class)
                    ->findAndUpdate()
                    ->field('id')->equals($part->id)
                    ->field('is_done')->set(1)
                    ->getQuery()
                    ->execute();
                }

                $instance->file = $this->fileRepository->fetchOne($instance->data->request_id, $instance->data->file_id);

                $instance->is_done = $this->filePartRepository->isFileDone($instance->file);
            }

            return $instance;
        }
        catch(\Exception $e)
        {
            if(isset($instance) && $instance->data)
            {
                $this->destroyRequestService->destroy($instance->data->request_id);
            }

            throw $e;
        }
    }
}
