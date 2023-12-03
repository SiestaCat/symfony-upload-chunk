<?php

namespace Siestacat\UploadChunkBundle\Document;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Siestacat\UploadChunkBundle\Repository\RequestRepository;

#[MongoDB\Document(collection: 'upload_chunk_request', repositoryClass: RequestRepository::class)]
#[MongoDB\HasLifecycleCallbacks]
#[MongoDB\Index(['date_created'])]
class Request
{
    #[MongoDB\Id(strategy: 'none')]
    public string $request_id;

    #[MongoDB\Field(type: 'string')]
    public ?string $extra_data = null;

    #[MongoDB\Field(type: 'date_immutable')]
    public ?DateTimeImmutable $date_created = null;

    public function setExtraData(mixed $unserialized_extra_data):self
    {
        $this->extra_data = serialize($unserialized_extra_data);

        return $this;
    }

    public function getExtraData():mixed
    {
        return unserialize($this->extra_data);
    }

    #[MongoDB\PrePersist]
    public function onPrePersist()
    {
        $this->date_created = new DateTimeImmutable;
    }
}
