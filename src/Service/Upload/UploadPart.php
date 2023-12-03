<?php

namespace Siestacat\UploadChunkBundle\Service\Upload;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Form\Upload\UploadPartType;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestFileRepository;
use Siestacat\UploadChunkBundle\Service\Upload\Data\UploadPartInstance;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFilePart;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestFilePartRepository;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestRepository;
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
        private UploadChunkRequestRepository $uploadChunkRequestRepository,
        private UploadChunkRequestFilePartRepository $uploadChunkRequestFilePartRepository,
        private UploadChunkRequestFileRepository $uploadChunkRequestFileRepository,
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
                $part = $this->uploadChunkRequestFilePartRepository->fetchOne($instance->data->request_id, $instance->data->file_id, $instance->data->part_index);

                $file = $this->uploadChunkRequestFileRepository->fetchOne($instance->data->request_id, $instance->data->file_id);
                
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

                    $this->uploadChunkRequestFilePartRepository->documentManager->createQueryBuilder(UploadChunkRequestFilePart::class)
                    ->findAndUpdate()
                    ->field('id')->equals($part->id)
                    ->field('is_done')->set(1)
                    ->getQuery()
                    ->execute();
                }
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
