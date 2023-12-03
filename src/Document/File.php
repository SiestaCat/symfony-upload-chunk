<?php

namespace Siestacat\UploadChunkBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Siestacat\UploadChunkBundle\Repository\FileRepository;

#[MongoDB\Document(collection: 'upload_chunk_request_file', repositoryClass: FileRepository::class)]
#[MongoDB\HasLifecycleCallbacks]
#[MongoDB\Index(['request_id'])]
#[MongoDB\Index(['file_id'])]
class File
{

    const CHUNK_SIZE_MIN = 1000000; //1MB
    const CHUNK_SIZE_MAX = 10000000; //10MB

    #[MongoDB\Id]
    public string $id;

    #[MongoDB\Field(type: 'string')]
    public ?string $request_id = null;

    #[MongoDB\Field(type: 'string')]
    public string $file_id;

    #[MongoDB\Field(type: 'string')]
    public string $name;

    #[MongoDB\Field(type: 'integer')]
    public int $size;

    #[MongoDB\Field(type: 'integer')]
    public ?int $chunk_size = null;

    #[MongoDB\Field(type: 'integer')]
    public ?int $parts_count = null;

    #[MongoDB\Field(type: 'integer')]
    public int $parts_uploaded = 0;

    #[MongoDB\Field(type: 'integer')]
    public int $is_done = 0;

    #[MongoDB\Field(type: 'string')]
    public ?string $tmp_path = null;

    #[MongoDB\PrePersist]
    public function onPrePersist()
    {
        $this->chunk_size = self::calculateChunkSize($this->size, self::CHUNK_SIZE_MIN, self::CHUNK_SIZE_MAX);

        $this->parts_count = ceil($this->size / $this->chunk_size);
    }

    private static function calculateChunkSize(int $size, int $min, int $max):int
    {
        $chunk_size = intval($size / 100);

        $chunk_size = $chunk_size < $min ? $min : ($chunk_size > $max ? $max : $chunk_size);

        $chunk_size = $size <= $chunk_size ? $size : $chunk_size;

        return $chunk_size;
    }
}
