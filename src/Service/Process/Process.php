<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Document\File;
use Siestacat\UploadChunkBundle\Document\FilePart;
use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessData;
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

    public function process(?ProcessData $data = null):ProcessInstance
    {

        try
        {
            $instance = new ProcessInstance
            (
                $data === null ? $this->formFactory->create(ProcessType::class)->handleRequest($this->requestStack->getCurrentRequest()) : null,
                $data
            );

            if($instance->form && !$instance->form->isValid() && $instance->data)
            {
                $this->destroyRequestService->destroy($instance->data->request_id);
            }

            if(($instance->form && $instance->form->isValid()) || $instance->form === null)
            {
                $instance->request = $this->requestRepository->findOneBy(['request_id' => $instance->data->request_id]);

                $instance->file = $this->fileRepository->fetchOne($instance->data->request_id, $instance->data->file_id);

                if($instance->file->is_done === 0)
                {
                    $this->joinChunkedFiles($instance->file);

                    $this->fileRepository->documentManager->createQueryBuilder(File::class)
                    ->findAndUpdate()
                    ->field('request_id')->equals($instance->file->request_id)
                    ->field('file_id')->equals($instance->file->file_id)
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

    public function postProcess(ProcessInstance $instance):int
    {
        return $this->postProcessFile($instance->file);
    }

    public function postProcessFile(File $file):int
    {
        $this->fileRepository->deleteFile($file);
        $this->fileRepository->documentManager->flush();

        return $this->processRequestService->getRequestIdStatus($file->request_id);
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
