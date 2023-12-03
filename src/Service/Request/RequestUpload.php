<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service\Request;

use Siestacat\UploadChunkBundle\Form\Request\UploadRequestType;
use Siestacat\UploadChunkBundle\Service\Request\Data\RequestUploadInstance;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Siestacat\UploadChunkBundle\Document\File;
use Siestacat\UploadChunkBundle\Document\FilePart;
use Siestacat\UploadChunkBundle\Document\Request;
use Siestacat\UploadChunkBundle\Service\DestroyRequest;
use Siestacat\UploadChunkBundle\Service\FetchDir;
use Siestacat\UploadChunkBundle\Service\RequestSession;
use Symfony\Component\HttpKernel\KernelInterface;

class RequestUpload
{
    public function __construct
    (
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private DocumentManager $documentManager,
        private RequestSession $requestSessionService,
        private DestroyRequest $destroyRequestService,
        private KernelInterface $kernel,
        private FetchDir $fetchDirService
    )
    {}

    public function createRequest(mixed $extra_data = null):RequestUploadInstance
    {
        try
        {
            $request_instance = new RequestUploadInstance
            (
                $this->formFactory->create(UploadRequestType::class)->handleRequest($this->requestStack->getCurrentRequest()),
                $this->createRequestDocument($extra_data)
            );

            if($request_instance->form->isValid())
            {
                $this->documentManager->persist($request_instance->request_document);

                foreach($request_instance->files as $file_document)
                {
                    $file_document->request_id = $request_instance->request_document->request_id;

                    $file_document->tmp_path = $this->fetchDirService->getFile('joined/' . $file_document->request_id . '/' . $file_document->file_id);

                    $this->documentManager->persist($file_document);

                    $parts = $this->persistFileParts($file_document);

                    foreach($parts as $part)
                    {
                        $request_instance->response_parts[] = (object) [
                            'file_id' => $file_document->file_id,
                            'index' => $part->index,
                            'size' => $part->size,
                            'start' => $part->start,
                            'end' => $part->end
                        ];
                    }
                }

                $this->documentManager->flush();

                $this->requestSessionService->create($request_instance->request_document->request_id);
            }

            return $request_instance;
        }
        catch(\Exception $e)
        {
            if(isset($request_instance) && $request_instance->request_document)
            {
                $this->destroyRequestService->destroy($request_instance->request_document->request_id);
            }
            throw $e;
        }
        
    }

    private function createRequestDocument(mixed $extra_data = null):Request
    {
        $request_document = new Request();

        $request_document->setExtraData($extra_data);

        $request_document->request_id = hash('sha512', random_bytes(128));

        return $request_document;
    }

    /**
     * @return FilePart[]
     */
    private function persistFileParts(File $file_document):array
    {

        $parts = [];

        $start = 0;

        for($part_index = 1; $part_index <= $file_document->parts_count; $part_index++)
        {
            $part_document = new FilePart;

            $part_document->request_id = $file_document->request_id;

            $part_document->file_native_id = $file_document->file_id;

            $part_document->index = $part_index;

            $part_document->start = $start;

            $end = $start + $file_document->chunk_size;

            $part_document->end = $end >= $file_document->size ? $file_document->size : $end;
            $part_document->size = $part_document->end - $part_document->start;

            $start = $part_document->end;

            $part_document->tmp_path = $this->fetchDirService->getFile('parts/' . $part_document->request_id . '/' . $part_document->file_native_id . '/' . $part_document->index);

            $this->documentManager->persist($part_document);

            $parts[] = $part_document;

            if($part_document->end === $file_document->size) break;
        }

        if($file_document->parts_count !== count($parts)) throw new \Exception(sprintf('Parts array must have %s elements. Only have %d', $file_document->parts_count, count($parts)));

        return $parts;
    }
    
}
