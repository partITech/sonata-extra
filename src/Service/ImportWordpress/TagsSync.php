<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use App\Entity\SonataClassificationTag;
use App\Repository\SonataClassificationContextRepository;
use App\Repository\SonataClassificationTagRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Tags;
use Psr\Log\LoggerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TagsSync
{
    private ?array $tags = null;
    
    public function __construct(
        private readonly Tags $tagsApi,
        private readonly EntityManagerInterface $entityManager,
        private readonly SonataClassificationContextRepository $contextRepository,
        private readonly SiteManagerInterface $siteManager,
        private readonly LoggerInterface $logger,
        private readonly SonataClassificationTagRepository $classificationTagRepository,
        private readonly Event $event,
    ) {
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function sync(): void
    {
        $site = $this->siteManager->findOneBy(['isDefault' => true]);
        $context = $this->contextRepository->findOneBy(['id' => 'default']);
        $this->getTags();
        $this->event->setCurrentStep(Event::TAGS_STEP);
        $this->event->setCount(count($this->tags));
        
        foreach ($this->tags as $key => $tag) {
            $this->event->setJob($tag['name']);
            $entity = $this->classificationTagRepository->findOneBy(['name' => $tag['name']]);
            if (is_null($entity)) {
                $entity = new SonataClassificationTag();
                $entity->setName($tag['name']);
                $entity->setEnabled(true);
                $entity->setSlug($tag['slug']);
                $entity->setContext($context);
                $entity->setUpdatedAt(new DateTimeImmutable());
                $entity->setCreatedAt(new DateTimeImmutable());
                $entity->setSite($site);

                try {
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                } catch (Exception $e) {
                    $this->logger->error(sprintf('Failed to process category "%s": %s', $tag['name'], $e->getMessage()));
                }
            }
            $this->tags[$key]['entity'] = $entity;
        }
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getTags(): ?array
    {
        if (is_null($this->tags)) {
            $this->tags = $this->tagsApi->getAll();
        }

        return $this->tags;
    }

    public function getTagByWpId(int $id): ?object
    {
        foreach ($this->tags as $tag) {
            if ($tag['id'] !== $id) {
                continue;
            }

            return $tag['entity'] ?? null;
        }

        return null;
    }
}
