<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Document\File;
use Siestacat\UploadChunkBundle\Document\FilePart;
use Siestacat\UploadChunkBundle\Form\Process\ProcessType;
use Siestacat\UploadChunkBundle\Repository\FilePartRepository;
use Siestacat\UploadChunkBundle\Repository\RequestRepository;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;
use Siestacat\UploadChunkBundle\Service\Process\Data\ProcessInstance;
use Symfony\Component\HttpKernel\KernelInterface;

class Process
{
    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private RequestRepository $requestRepository,
        private FileRepository $fileRepository,
        private FilePartRepository $filePartRepository,
        private KernelInterface $kernel,
        private DestroyRequest $destroyRequestService,
        private ProcessRequest $processRequestService
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

                $instance->request = $this->requestRepository->findOneBy(['request_id' => $instance->data->request_id]);

                $instance->file = $this->fileRepository->fetchOne($instance->data->request_id, $instance->data->file_id);

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
        $this->fileRepository->deleteFile($instance->file);
        $this->fileRepository->documentManager->flush();

        return $this->processRequestService->getRequestIdStatus($instance->data->request_id);
    }

    private function joinChunkedFiles(File $file)
    {
        $resource = fopen($file->tmp_path, 'wb');

        foreach($this->filePartRepository->findBy(['request_id' => $file->request_id, 'file_native_id' => $file->file_id]) as $part)
        {
            $this->joinChunkedFilePart($resource, $part);
        }

        fclose($resource);
    }

    private function joinChunkedFilePart($resource, FilePart $part)
    {
        $resource_part = fopen($part->tmp_path, 'rb');

        while ($data = fread($resource_part, 1024))
        {
            fwrite($resource, $data);
        }

        fclose($resource_part);
    }
}
