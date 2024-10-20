<?php
    
    namespace Partitech\SonataExtra\Service;
    
    use App\Entity\SonataPagePage;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\EntityRepository;
    use Partitech\SonataExtra\Entity\Article;
    use Partitech\SonataExtra\Repository\ArticleRepository;
    use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
    use Sonata\MediaBundle\Model\MediaManagerInterface;
    use Sonata\MediaBundle\Provider\Pool;
    use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
    use Sonata\PageBundle\Route\CmsPageRouter;
    use Sonata\PageBundle\Site\SiteSelectorInterface;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Routing\RequestContext;
    class SiteMap
    {
        private string $pageClass;
        private string $articleClass;
        private string $siteClass;
        private EntityRepository $pageRepository;
        private EntityRepository $siteRepository;
        
        private array $sitesList = [];
        
        
        public function __construct(
            private UrlGeneratorInterface $urlGenerator,
            private RequestContext $context,
            private CmsPageRouter $cmsPageRouter,
            private EntityManagerInterface $entityManager,
            private ArticleRepository $articleRepository,
            private ParameterBagInterface $parameterBag,
            private readonly MediaManagerInterface $mediaManager,
            private readonly Pool $pool,
            private readonly SiteSelectorInterface $siteSelector,
            protected RequestStack $requestStack,
            protected UrlGeneratorInterface $router
        ) {
            $this->articleClass = Article::class;
            $this->pageClass = $this->parameterBag->get('sonata.page.page.class');
            $this->siteClass = $this->parameterBag->get('sonata.page.site.class');
            $this->pageRepository = $this->entityManager->getRepository($this->pageClass);
            $this->siteRepository = $this->entityManager->getRepository($this->siteClass);
        }
        
        public function getPages(): array
        {
            $pages = [];
            $site = $this->siteSelector->retrieve();
            $pagesList = $this->pageRepository->findBy(['site'=>$site->getId(), 'type' => 'sonata.page.service.default']);
            
            
            /** @var Article $article */
            foreach($pagesList as $page){
                $pages[] = $this->getTranslations($page);
            }
            
            return $pages;
        }
        
        public function getArticles(): array
        {
            $articles = [];
            $site = $this->siteSelector->retrieve();
            $articlesList = $this->articleRepository->findPublishedBySite($site);
            
            /** @var Article $article */
            foreach($articlesList as $article){
                $articles[] = $this->getTranslations($article);
            }
            
            return $articles;
        }
        
        
        public function getImages(): array
        {
            $site = $this->siteSelector->retrieve();
            $mediaList = $this->mediaManager->findBy(['enabled' => true]);
            $scheme= $this->requestStack->getCurrentRequest()->getScheme();
            $host = $scheme . '://' . $site->getHost();
            $medias = [];
            
            foreach($mediaList as $media){
                $provider = $this->pool->getProvider($media->getProviderName());
                $format = $provider->getFormatName($media, 'reference');
                $publicUrl = $provider->generatePublicUrl($media, $format);
                $medias[] = new UrlConcrete( loc:$host.$publicUrl, lastmod: $media->getUpdatedAt());
            }
            
            return $medias;
        }
        private function getSite(int $id){
            
            if(isset($this->sitesList[$id])){
                return $this->sitesList[$id];
            }
            $site = $this->siteRepository->find($id);
            if(is_null($site)){
               return null;
            }
            $this->sitesList[$id] = $site;
            return $site;
        }
        
        private function getTranslations(SonataPagePage|Article $articles): array {
            $translations = $articles->getTranslations();
            $siteId = $this->siteSelector->retrieve()->getId();
            $withKey = array_filter($translations, function($element) use ($siteId) {
                return isset($element['site']) && $element['site'] == $siteId;
            });
            
            $withoutKey = array_filter($translations, function($element) use ($siteId) {
                return !isset($element['site']) || $element['site'] != $siteId;
            });
            
            return ['current' => $withKey, 'translations' => $withoutKey];
        }
    }