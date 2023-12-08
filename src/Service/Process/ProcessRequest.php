<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Process;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Siestacat\UploadChunkBundle\Repository\FileRepository;
use Siestacat\UploadChunkBundle\Form\ProcessRequest\ProcessRequestType;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;

class ProcessRequest
{

    const POST_PROCESS_HAS_PENDING_FILES    = 0;
    const POST_PROCESS_FULLY_PROCESSED      = 1;

    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private FileRepository $fileRepository,
        private DestroyRequest $destroyRequestService
    )
    {}

    public function getStatus():int
    {
        try
        {
            $form = $this->formFactory->create(ProcessRequestType::class)->handleRequest($this->requestStack->getCurrentRequest());

            /**
             * @var ?string
             */
            $request_id = $form->get('request_id') ? $form->get('request_id')->getData() : null;

            if(!$form->isValid() && $request_id !== null)
            {
                $this->destroyRequestService->destroy($request_id);
            }

            if($form->isValid())
            {
                return $this->getRequestIdStatus($request_id);
            }

            return self::POST_PROCESS_HAS_PENDING_FILES;
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

    public function getRequestIdStatus(?string $request_id):int
    {
        if($request_id !== null && $this->fileRepository->getRequestPendingCount($request_id) === 0)
        {
            $this->destroyRequestService->destroy($request_id);
            return self::POST_PROCESS_FULLY_PROCESSED;
        }

        return self::POST_PROCESS_HAS_PENDING_FILES;
    }
}
