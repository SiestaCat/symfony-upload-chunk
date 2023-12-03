<?php

namespace Siestacat\UploadChunkBundle\Service\Process;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestFileRepository;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFile;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFilePart;
use Siestacat\UploadChunkBundle\Form\Process\ProcessType;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestFilePartRepository;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestRepository;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;
use Siestacat\UploadChunkBundle\Service\Process\Data\ProcessInstance;
use Symfony\Component\HttpKernel\KernelInterface;

class Process
{

    const POST_PROCESS_HAS_PENDING_FILES    = 0;
    const POST_PROCESS_FULLY_PROCESSED      = 1;

    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private UploadChunkRequestRepository $uploadChunkRequestRepository,
        private UploadChunkRequestFileRepository $uploadChunkRequestFileRepository,
        private UploadChunkRequestFilePartRepository $uploadChunkRequestFilePartRepository,
        private KernelInterface $kernel,
        private DestroyRequest $destroyRequestService
    )
    {}

    public function process():ProcessInstance
    {

        try
        {
            $instance = new ProcessInstance
            (
                $this->formFactory->create(ProcessType::class)->handleRequest($this->requestStack->getCurrentRequest())
            );

            if(!$instance->form->isValid() && $instance->data)
            {
                $this->destroyRequestService->destroy($instance->data->request_id);
            }

            if($instance->form->isValid())
            {

                $instance->request = $this->uploadChunkRequestRepository->findOneBy(['request_id' => $instance->data->request_id]);

                $instance->file = $this->uploadChunkRequestFileRepository->fetchOne($instance->data->request_id, $instance->data->file_id);

                $this->joinChunkedFiles($instance->file);
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

    public function postProcess(ProcessInstance $instance):int
    {
        $this->uploadChunkRequestFileRepository->deleteFile($instance->file);
        $this->uploadChunkRequestFileRepository->documentManager->flush();

        $files_pending_count =
        $this->uploadChunkRequestFileRepository->documentManager->createQueryBuilder(UploadChunkRequestFile::class)
        ->field('request_id')->equals($instance->data->request_id)
        ->count()->getQuery()->execute();

        if(!is_int($files_pending_count)) throw new \Exception('Doctrine ODM count response is not int');

        if($files_pending_count === 0)
        {
            $this->destroyRequestService->destroy($instance->request->request_id);
            return self::POST_PROCESS_FULLY_PROCESSED;
        }

        return self::POST_PROCESS_HAS_PENDING_FILES;
    }

    private function joinChunkedFiles(UploadChunkRequestFile $file)
    {
        $resource = fopen($file->tmp_path, 'wb');

        foreach($this->uploadChunkRequestFilePartRepository->findBy(['request_id' => $file->request_id, 'file_native_id' => $file->file_id]) as $part)
        {
            $this->joinChunkedFilePart($resource, $part);
        }

        fclose($resource);
    }

    private function joinChunkedFilePart($resource, UploadChunkRequestFilePart $part)
    {
        $resource_part = fopen($part->tmp_path, 'rb');

        while ($data = fread($resource_part, 1024))
        {
            fwrite($resource, $data);
        }

        fclose($resource_part);
    }
}
