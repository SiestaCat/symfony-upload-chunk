<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Form\ProcessRequest\ProcessRequestType;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;
use Siestacat\UploadChunkBundle\Service\Process\Data\ProcessRequestInstance;
use Siestacat\UploadChunkBundle\Repository\RequestRepository;

class ProcessRequest
{

    const POST_PROCESS_HAS_PENDING_FILES    = 0;
    const POST_PROCESS_FULLY_PROCESSED      = 1;

    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private FileRepository $fileRepository,
        private DestroyRequest $destroyRequestService,
        private RequestRepository $requestRepository
    )
    {}

    public function getProcessRequest():ProcessRequestInstance
    {
        try
        {
            $instance = new ProcessRequestInstance($this->formFactory->create(ProcessRequestType::class)->handleRequest($this->requestStack->getCurrentRequest()));

            if(!$instance->form->isValid())
            {
                $this->destroyRequestService->destroy($instance->data->request_id);
            }

            if($instance->form->isValid())
            {
                $instance->request = $this->requestRepository->findOneBy(['request_id' => $instance->data->request_id]);

                $instance->pending_count = $this->getPendingCount($instance->request->request_id);
                $instance->status = $this->getStatusByCount($instance->pending_count);

                if($instance->status === self::POST_PROCESS_FULLY_PROCESSED)
                {
                    $this->destroyRequestService->destroy($instance->request->request_id);
                }
            }

            return $instance;
        }
        catch(\Exception $e)
        {

            if(isset($request_id) && $request_id !== null)
            {
                $this->destroyRequestService->destroy($request_id);
            }

            throw $e;
        }
    }

    public function getPendingCount(string $request_id):int
    {
        return $this->fileRepository->getRequestPendingCount($request_id);
    }

    public function getStatusByCount(int $pending_count):int
    {
        return $pending_count === 0 ? self::POST_PROCESS_FULLY_PROCESSED : self::POST_PROCESS_HAS_PENDING_FILES;
    }

    public function getRequestIdStatus(?string $request_id):int
    {
        return $request_id === null ? self::POST_PROCESS_HAS_PENDING_FILES : $this->getStatusByCount($this->getPendingCount($request_id));
    }
}
