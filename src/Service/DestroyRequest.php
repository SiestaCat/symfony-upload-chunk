<?php

namespace Siestacat\UploadChunkBundle\Service;

use Siestacat\UploadChunkBundle\Repository\RequestRepository;

class DestroyRequest
{

    public function __construct(private RequestSession $requestSessionService, private RequestRepository $requestRepository)
    {}

    public function destroy(string $request_id)
    {
        $this->requestSessionService->delete($request_id);

        if($request_id !== null) $this->requestRepository->deleteCascade($request_id);
    }

}
