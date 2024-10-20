<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use App\Entity\SonataClassificationCategory;
use App\Entity\SonataClassificationContext;
use App\Repository\SonataClassificationCategoryRepository;
use App\Repository\SonataClassificationContextRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Categories;
use Psr\Log\LoggerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CategoriesSync
{
    private ?array $categories = null;
    private ?SonataClassificationContext $context = null;

    public function __construct(
        private readonly Categories $categoriesApi,
        private readonly SonataClassificationCategoryRepository $classificationCategoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SonataClassificationContextRepository $contextRepository,
        private readonly SiteManagerInterface $siteManager,
        private readonly LoggerInterface $logger,
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
        $this->context = $this->contextRepository->findOneBy(['id' => 'default']);
        $this->getCategories();
        $this->event->setCurrentStep(Event::CATEGORIES_STEP);
        $this->event->setCount(count($this->categories));
        $this->syncChild(0);
    }

    public function createEntity(array $data): void
    {
        $this->event->setJob($data['name']);
        
        $category = $this->classificationCategoryRepository->findOneBy(['name' => $data['name']]);
        
        if ($category instanceof SonataClassificationCategory) {
            return;
        }
        $site = $this->siteManager->findOneBy(['isDefault' => true]);

        $category = new SonataClassificationCategory();
        $category->setName($data['name']);
        $category->setParent((0 === $data['parent']) ? null : $this->getEntityByWpId($data['parent']));
        $category->setSlug($data['slug']);
        $category->setEnabled(true);
        $category->setSite($site);
        $category->setContext($this->context);
        $category->setCreatedAt(new DateTimeImmutable());
        $category->setUpdatedAt(new DateTimeImmutable());

        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error(sprintf('Failed to process category "%s": %s', $data['name'], $e->getMessage()));

            return;
        }

        $this->syncChild($data['id']);
    }

    public function syncChild($id): void
    {
        $children = $this->filterByParentId($id);
        foreach ($children as $child) {
            $this->createEntity($child);
        }
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCategories(): ?array
    {
        if (is_null($this->categories)) {
            $this->categories = $this->categoriesApi->getAll();
        }

        return $this->categories;
    }

    public function filterByParentId($id): array
    {
        $list = [];
        foreach ($this->categories as $category) {
            if ($category['parent'] === $id) {
                $list[] = $category;
            }
        }

        return $list;
    }

    public function getEntityByWpId($id): ?SonataClassificationCategory
    {
        foreach ($this->categories as $category) {
            if ($category['id'] === $id) {
                return $this->classificationCategoryRepository->findOneBy(['name' => $category['name']]);
            }
        }

        return null;
    }
}
