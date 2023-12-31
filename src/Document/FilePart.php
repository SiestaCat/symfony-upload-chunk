<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Siestacat\UploadChunkBundle\Repository\FilePartRepository;

#[MongoDB\Document(collection: 'upload_chunk_request_file_part', repositoryClass: FilePartRepository::class)]
#[MongoDB\HasLifecycleCallbacks]
#[MongoDB\Index(['file_native_id'])]
#[MongoDB\Index(['is_done'])]
class FilePart
{

    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    public ?string $request_id = null;

    #[MongoDB\Field(type: 'string')]
    public ?string $file_native_id = null;

    #[MongoDB\Field(type: 'int')]
    public int $index;

    #[MongoDB\Field(type: 'int')]
    public ?int $size = null;

    #[MongoDB\Field(type: 'int')]
    public ?int $start = null;

    #[MongoDB\Field(type: 'int')]
    public ?int $end = null;

    #[MongoDB\Field(type: 'string')]
    public ?string $tmp_path = null;

    #[MongoDB\Field(type: 'int')]
    public int $is_done = 0;

    public function setIsDone(int $is_done):self
    {
        $this->is_done = $is_done;

        return $this;
    }

    
}
