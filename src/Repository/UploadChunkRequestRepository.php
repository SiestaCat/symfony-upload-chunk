<?php

namespace Siestacat\UploadChunkBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequest;

class UploadChunkRequestRepository extends ServiceDocumentRepository
{

    public function __construct(public DocumentManager $documentManager, ManagerRegistry $registry, private UploadChunkRequestFilePartRepository $part_repository, private UploadChunkRequestFileRepository $file_repository)
    {
        parent::__construct($registry, UploadChunkRequest::class);
    }

    public function deleteCascade(string $request_id, int $clear_at = 10):void
    {

        $this->part_repository->deleteByRequestId($request_id, $clear_at);
        $this->file_repository->deleteByRequestId($request_id, $clear_at);

        /**
         * @var UploadChunkRequest
         */
        $request_document = $this->findOneBy(['request_id' => $request_id]);

        $this->documentManager->remove($this->find($request_document->request_id));

        $this->documentManager->flush();
        $this->documentManager->clear();

        
    }
}