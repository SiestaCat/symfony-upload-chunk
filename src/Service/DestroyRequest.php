<?php

namespace Siestacat\UploadChunkBundle\Service;

use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestRepository;

class DestroyRequest
{

    public function __construct(private RequestSession $requestSessionService, private UploadChunkRequestRepository $uploadChunkRequestRepository)
    {}

    public function destroy(string $request_id)
    {
        $this->requestSessionService->delete($request_id);

        if($request_id !== null) $this->uploadChunkRequestRepository->deleteCascade($request_id);
    }

}
