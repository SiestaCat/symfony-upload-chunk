<?php

namespace Siestacat\UploadChunkBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class RequestSession
{

    const SESSION_NAME = 'upload.chunk.%s';

    public function __construct
    (
        private RequestStack $requestStack
    )
    {}

    public function exists(string $request_id):bool
    {
        return $this->getRequestSession()->has(self::getSessionName($request_id));
    }

    public function create(string $request_id):void
    {
        $this->getRequestSession()->set(self::getSessionName($request_id), true);
    }

    public function delete(?string $request_id):void
    {
        if($request_id !== null && $this->exists($request_id))
        {
            $this->getRequestSession()->remove(self::getSessionName($request_id));
        }
    }

    private function getRequestSession():SessionInterface
    {
        return $this->requestStack->getCurrentRequest()->getSession();
    }

    private static function getSessionName(string $request_id):string
    {
        return sprintf(self::SESSION_NAME, $request_id);
    }

}
