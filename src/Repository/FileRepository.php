<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Siestacat\UploadChunkBundle\Document\File;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Siestacat\UnlinkNoEmptyFolder\ClearFolderAfterUnlink;

class FileRepository extends ServiceDocumentRepository
{
    public function __construct(public DocumentManager $documentManager, ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function fetchOne(string $request_id, string $file_id):?File
    {
        return $this->findOneBy(['request_id' => $request_id, 'file_id' => $file_id]);
    }

    public function getRequestPendingCount(string $request_id):int
    {
        return $this->documentManager->createQueryBuilder(File::class)
        ->field('request_id')->equals($request_id)
        ->field('is_done')->equals(0)
        ->count()->getQuery()->execute();
    }

    public function deleteByRequestId(string $request_id, int $clear_at):void
    {
        /**
         * @var File[]
         */
        $iterator = 
        $this->documentManager->createQueryBuilder(File::class)
        ->select('id', 'tmp_path')
        ->field('request_id')->equals($request_id)
        ->getQuery()
        ->execute();

        $index = 0;

        foreach($iterator as $file) 
        {
            if($index >= $clear_at)
            {
                $this->documentManager->flush();
                $this->documentManager->clear();
                $index = 0;
            }

            $index++;

            $this->deleteFile($file);
        }

        $this->documentManager->flush();
        $this->documentManager->clear();
    }

    public function deleteFile(?File $file):void
    {
        if($file === null) return;
        if(is_file($file->tmp_path)) ClearFolderAfterUnlink::unlink($file->tmp_path);
        $this->documentManager->remove($this->find($file->id));
    }
}