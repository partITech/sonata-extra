<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use DateTime;
use Exception;
use Partitech\SonataExtra\Entity\Article;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Partitech\SonataExtra\Repository\ArticleRepository;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Posts;
use Psr\Log\LoggerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PostSync
{
    private ?array $posts = null;

    public function __construct(
        private readonly UserSync $userSync,
        private readonly TagsSync $tagsSync,
        private readonly MediasSync $mediasSync,
        private readonly CategoriesSync $categoriesSync,
        private readonly Posts $postsApi,
        private readonly ArticleRepository $articleRepository,
        private readonly LoggerInterface $logger,
        private readonly SiteManagerInterface $siteManager,
        private readonly Event $event
    ) {
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function sync(): void
    {
        $site = $this->siteManager->findOneBy(['isDefault' => true]);
        $posts = $this->getPosts();
        $this->event->setCurrentStep(Event::POSTS_STEP);
        $this->event->setCount(count($posts));
        
        foreach ($posts as $post) {
            $titre = html_entity_decode($post['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $this->event->setJob($titre);
            
            $content = $post['content']['rendered'];
            if(empty($content)){
                continue;
            }
            
            $article = $this->articleRepository->findOneBy(['slug' =>$post['slug']]);
            
            if(is_null($article)){
                $article = new Article();
            }
            
            if (isset($post['featured_media']) && is_int($post['featured_media'])) {
                $featuredImage = $this->mediasSync->getById($post['featured_media']);
                $article->setFeaturedImage($featuredImage);
            }

            if (is_array($post['categories'])) {
                foreach ($post['categories'] as $wpCategory) {
                    $category = $this->categoriesSync->getEntityByWpId($wpCategory);
                    $article->addCategory($category);
                }
            }

            if (is_array($post['tags'])) {
                foreach ($post['tags'] as $wpTag) {
                    $tag = $this->tagsSync->getTagByWpId($wpTag);
                    $article->addTag($tag);
                }
            }

            $author = $this->userSync->getUserByWpId($post['author']);
            $article->setAuthor($author);

            $article->setSite($site);

            /* Titles */
            $article->setTitle($titre);
            $article->setSeoOgTitle($titre);
            $article->setSeoTitle($titre);
            /* Descriptions */
            $article->setSeoOgDescription($post['yoast_head_json']['og_description'] ?? null);
            $article->setSeoDescription($post['yoast_head_json']['og_description'] ?? null);
            
            $content = Tools::replaceImage($content, $this->mediasSync->getMapping());
            
            $article->setContent($content);
            $article->setSlug($post['slug']);
            
            $status = $post['status'] === 'publish' ? ArticleStatus::PUBLISHED : ArticleStatus::UNPUBLISHED;
            $article->setStatus($status->value);
            $article->setPublishedAt(new DateTime($post['date']));
            try {
                $this->articleRepository->save($article, true);
            } catch (Exception $e) {
                $this->logger->error(sprintf('Failed to process Post "%s": %s', $post['id'].$post['title']['rendered'], $e->getMessage()));
            }
        }
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getPosts(): ?array
    {
        if (is_null($this->posts)) {
            $this->posts = $this->postsApi->getAll();
        }

        return $this->posts;
    }
}