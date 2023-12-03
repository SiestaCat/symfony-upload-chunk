<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Siestacat\UploadChunkBundle\Document\FilePart;
use Siestacat\UnlinkNoEmptyFolder\ClearFolderAfterUnlink;

class FilePartRepository extends ServiceDocumentRepository
{

    public function __construct(public DocumentManager $documentManager, ManagerRegistry $registry)
    {
        parent::__construct($registry, FilePart::class);
    }

    public function checkExists(string $request_id, string $file_native_id, int $index):bool
    {
        return $this->fetchOne($request_id, $file_native_id, $index) !== null;
    }

    public function fetchOne(string $request_id, string $file_native_id, int $index):?FilePart
    {
        return $this->findOneBy(['request_id' => $request_id, 'file_native_id' => $file_native_id, 'index' => $index]);
    }

    public function deleteByRequestId(string $request_id, int $clear_at):void
    {
        /**
         * @var FilePart[]
         */
        $iterator = 
        $this->documentManager->createQueryBuilder(FilePart::class)
        ->select('id', 'tmp_path')
        ->field('request_id')->equals($request_id)
        ->getQuery()
        ->execute();

        $clear_at = 10;

        $index = 0;

        foreach($iterator as $part) 
        {
            if($index >= $clear_at)
            {
                $this->documentManager->flush();
                $this->documentManager->clear();
                $index = 0;
            }

            $index++;

            $this->deletePart($part);
        }

        $this->documentManager->flush();
        $this->documentManager->clear();
    }

    public function deletePart(?FilePart $part):void
    {
        if($part === null) return;
        if(is_file($part->tmp_path))
        {
            $parent_dir = dirname(dirname($part->tmp_path));
            ClearFolderAfterUnlink::unlink($part->tmp_path);
            ClearFolderAfterUnlink::clear_dir($parent_dir);
        }
        $this->documentManager->remove($this->find($part->id));
    }
}