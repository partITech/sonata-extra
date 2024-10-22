<?php
    
    namespace Partitech\SonataExtra\EventListener;
    
    use Partitech\SonataExtra\Service\SiteMap;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Presta\SitemapBundle\Event\SitemapPopulateEvent;
    use Presta\SitemapBundle\Service\UrlContainerInterface;
    use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
    use Presta\SitemapBundle\Sitemap\Url\GoogleMultilangUrlDecorator;
    
    readonly class SitemapSubscriber implements EventSubscriberInterface
    {
        private UrlContainerInterface $urlContainer;
        public function __construct(
            private SiteMap $siteMap
        ) {}
        public function populate(SitemapPopulateEvent $event): void
        {
            $this->urlContainer = $event->getUrlContainer();
            
            $this->registerArticles();
            $this->registerPages();
            $this->registerImagesUrls();
        }
        
        public function registerImagesUrls(): void
        {
            $medias = $this->siteMap->getImages();
            
            foreach ($medias as $urlConcrete){
                $this->urlContainer->addUrl($urlConcrete, 'Medias');
            }
        }
        
        public function registerPages(): void
        {
            $pages = $this->siteMap->getPages();
            $this->registerTranslatedElements($pages, 'Pages');
        }
        
        public function registerArticles(): void
        {
            $articles = $this->siteMap->getArticles();
            $this->registerTranslatedElements($articles, 'Articles');
        }
        
        private function registerTranslatedElements(array $datas, string $section): void
        {
            foreach ($datas as $element){
                $current = reset($element['current']);
                if(!isset($current['routes'])){
                    continue;
                }
                
                $currentTranslation = reset($current['routes']);
                
                $url = new UrlConcrete($currentTranslation);
                $decoratedUrl = new GoogleMultilangUrlDecorator($url);
                $decoratedUrl->addLink($currentTranslation, $current['lang']);
                
                foreach($element['translations'] as $translation){
                    if(!isset($translation['routes'])){
                        continue;
                    }
                    $decoratedUrl->addLink(reset($translation['routes']), $translation['lang']);
                }
                
                $this->urlContainer->addUrl($decoratedUrl, $section);
            }
        }
        
        /**
         * @inheritDoc
         */
        public static function getSubscribedEvents(): array
        {
            return [
                SitemapPopulateEvent::class => 'populate',
            ];
        }
    }