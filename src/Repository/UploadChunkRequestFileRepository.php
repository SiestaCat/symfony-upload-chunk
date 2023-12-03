<?php

namespace Siestacat\UploadChunkBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Siestacat\UploadChunkBundle\Document\UploadChunkRequestFile;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Siestacat\UnlinkNoEmptyFolder\ClearFolderAfterUnlink;

class UploadChunkRequestFileRepository extends ServiceDocumentRepository
{
    public function __construct(public DocumentManager $documentManager, ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadChunkRequestFile::class);
    }

    public function fetchOne(string $request_id, string $file_id):?UploadChunkRequestFile
    {
        return $this->findOneBy(['request_id' => $request_id, 'file_id' => $file_id]);
    }

    public function deleteByRequestId(string $request_id, int $clear_at):void
    {
        /**
         * @var UploadChunkRequestFile[]
         */
        $iterator = 
        $this->documentManager->createQueryBuilder(UploadChunkRequestFile::class)
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

    public function deleteFile(?UploadChunkRequestFile $file):void
    {
        if($file === null) return;
        if(is_file($file->tmp_path)) ClearFolderAfterUnlink::unlink($file->tmp_path);
        $this->documentManager->remove($this->find($file->id));
    }
}