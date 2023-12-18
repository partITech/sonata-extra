<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use App\Entity\SonataMediaMedia;
use Exception;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Medias;
use Partitech\SonataExtra\Service\Media as MediaService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MediasSync
{
    private ?array $datas = null;
    private ?array $mapping = null;

    
    public function __construct(
        protected MediaService $mediaService,
        private readonly Medias $api,
        protected readonly LoggerInterface $logger,
        private readonly Event $event,
    ){}
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function sync(): void
    {
        if (is_null($this->datas)) {
            $this->datas = $this->api->getAll();
        }
        
        $this->event->setCurrentStep(Event::MEDIAS_STEP);
        $this->event->setCount(count($this->datas));

        foreach ($this->datas as $key => $data) {
            $this->event->setJob($data['title']['rendered'] . ': ' . $data['source_url']);
            $infos = $this->api->getById($data['id']);
            
            if (!isset($infos['source_url'])) {
                continue;
            }
            
            $mediaContent = $this->api->get($infos['source_url']);
            
            $media = $this->mediaService->createOrGetMediaByContent(
                name: $infos['title']['rendered'],
                content: $mediaContent,
            );
            
            $this->datas[$key]['entity'] = $media;

            try {
                $imgUrl = $this->mediaService->getPublicLink($media);
                $twig = $this->mediaService->renderMediaHtml($media);
                foreach ($infos['media_details']['sizes'] as $sizeInfos) {
                    $this->mapping[$sizeInfos['source_url']] = [
                        'url' => $imgUrl,
                        'twig' => $twig
                    ];
                }
                $this->mapping[$infos['source_url']] = [
                    'url' => $imgUrl,
                    'twig' => $twig
                ];
            } catch (Exception $e) {
                $this->logger->error(sprintf('Failed to get public media link %s' , $e->getMessage()));
            }
        }
    }
    
    public function getById(int $id): ?SonataMediaMedia
    {
        if(is_null($this->datas)){
            return null;
        }
        foreach($this->datas as $data){
            if($data['id'] === $id){
                return $data['entity'];
            }
        }
        return null;
    }
    
    public function getMapping(): array
    {
        return $this->mapping;
    }
}
