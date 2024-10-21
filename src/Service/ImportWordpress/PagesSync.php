<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use DateTime;
use Exception;
use Partitech\SonataExtra\Entity\Editor;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Partitech\SonataExtra\Repository\EditorRepository;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Pages;
use Psr\Log\LoggerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PagesSync
{
    private ?array $pages = null;
    public function __construct(
        private readonly MediasSync $mediasSync,
        private readonly Pages $pagesApi,
        private readonly LoggerInterface $logger,
        private readonly SiteManagerInterface $siteManager,
        private readonly Event $event,
        private readonly EditorRepository $editorRepository,
        
    ) {}

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function sync(): void
    {
        $pages = 1;
        $site = $this->siteManager->findOneBy(['isDefault' => true]);
        try {
            $pages = $this->getPages();
        } catch (Exception $e) {
        }
        $this->event->setCurrentStep(Event::PAGES_STEP);
        $this->event->setCount(count($pages));
        
        foreach ($pages as $page) {
            $titre = html_entity_decode($page['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $this->event->setJob($titre);
            
            $content = $page['content']['rendered'];
            if(empty($content)){
                continue;
            }
            $editor = $this->editorRepository->findOneBy(['title' => $titre]);
            if(!is_null($editor)){
                continue;
            }
            $editor = new Editor();
            $editor->setSite($site);
            $content = Tools::replaceImage($content, $this->mediasSync->getMapping());
            $editor->setContent($content);
            $editor->setPublishedAt(new DateTime($page['date']));
            $status = $page['status'] === 'publish' ? ArticleStatus::PUBLISHED : ArticleStatus::UNPUBLISHED;
            $editor->setStatus($status->value);
            $editor->setTypeEditor('ckeditor');
            $editor->setTranslationFromId(1);
            
            try {
                $this->editorRepository->save($editor, true);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Failed to process Page "%s": %s', $page['id'].$page['title']['rendered'], $e->getMessage()));
            }
        }
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getPages(): ?array
    {
        if (is_null($this->pages)) {
            $this->pages = $this->pagesApi->getAll();
        }

        return $this->pages;
    }
}