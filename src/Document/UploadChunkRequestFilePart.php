<?php

namespace Siestacat\UploadChunkBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Siestacat\UploadChunkBundle\Repository\UploadChunkRequestFilePartRepository;

#[MongoDB\Document(collection: 'upload_chunk_request_file_part', repositoryClass: UploadChunkRequestFilePartRepository::class)]
#[MongoDB\HasLifecycleCallbacks]
#[MongoDB\Index(['file_native_id'])]
#[MongoDB\Index(['is_done'])]
class UploadChunkRequestFilePart
{

    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    public ?string $request_id = null;

    #[MongoDB\Field(type: 'string')]
    public ?string $file_native_id = null;

    #[MongoDB\Field(type: 'integer')]
    public int $index;

    #[MongoDB\Field(type: 'integer')]
    public ?int $size = null;

    #[MongoDB\Field(type: 'integer')]
    public ?int $start = null;

    #[MongoDB\Field(type: 'integer')]
    public ?int $end = null;

    #[MongoDB\Field(type: 'string')]
    public ?string $tmp_path = null;

    #[MongoDB\Field(type: 'integer')]
    public int $is_done = 0;

    public function setIsDone(int $is_done):self
    {
        $this->is_done = $is_done;

        return $this;
    }

    
}
