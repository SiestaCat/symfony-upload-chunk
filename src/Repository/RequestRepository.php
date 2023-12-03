<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Siestacat\UploadChunkBundle\Document\Request;

class RequestRepository extends ServiceDocumentRepository
{

    public function __construct(public DocumentManager $documentManager, ManagerRegistry $registry, private FilePartRepository $filePartRepository, private FileRepository $fileRepository)
    {
        parent::__construct($registry, Request::class);
    }

    public function deleteCascade(string $request_id, int $clear_at = 10):void
    {

        $this->filePartRepository->deleteByRequestId($request_id, $clear_at);
        $this->fileRepository->deleteByRequestId($request_id, $clear_at);

        /**
         * @var Request
         */
        $request_document = $this->findOneBy(['request_id' => $request_id]);

        $this->documentManager->remove($this->find($request_document->request_id));

        $this->documentManager->flush();
        $this->documentManager->clear();

        
    }
}