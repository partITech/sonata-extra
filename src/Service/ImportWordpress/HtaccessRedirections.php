<?php
    
    namespace Partitech\SonataExtra\Service\ImportWordpress;
    
    use Doctrine\ORM\EntityRepository;
    use Partitech\SonataExtra\Service\ImportWordpress\Api\Config;
    use Sonata\PageBundle\Model\PageInterface;
    use Sonata\PageBundle\Model\SiteManagerInterface;
    use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class HtaccessRedirections
    {
        private array $fromTo = [];
        private array $categories = [
            'post' => [
                'route_name' => 'sonata_extra_blog_article',
                'slug' => null,
            ],
            'page' => [
                'route_name' => PageInterface::PAGE_ROUTE_CMS_NAME,
                'slug' => null,
            ],
            'category' => [
                'route_name' => 'sonata_extra_blog_category',
                'slug' => null,
            ],
            'post_tag' => [
                'route_name' => 'sonata_extra_blog_tag',
                'slug' => null,
            ],
//            'author' => [
//                'route_name' => null,
//            ],
        ];
        
        private string $siteRelativepath;
        private array  $urlList = [];
        private EntityRepository $pageRepository;
        public function __construct(
            private readonly Config $config,
            private readonly HttpClientInterface $httpClient,
            private readonly EntityManagerInterface $entityManager,
            private readonly ParameterBagInterface $parameterBag,
            private readonly SiteManagerInterface $siteManager,
        
        ){
            $page_class = $this->parameterBag->get('sonata.page.page.class');
            $this->pageRepository = $this->entityManager->getRepository($page_class);
            
        }
        
        /**
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        public function create(): self
        {
            $site                   = $this->siteManager->findOneBy(['isDefault' => true]);
            $this->siteRelativepath = $site->getRelativePath();
            $this->getInternalSlugs();
            $this->crawlRootSiteMap();
            $this->resolveUrls();
            return $this;
        }
        
        public function getHtaccess(): string
        {
            $content = null;
            foreach ($this->fromTo as $from => $to){
                $content .= 'Redirect 301' . $from . ' ' . $to  . PHP_EOL;
            }
            return $content;
        }
        private function getInternalSlugs(): void
        {
            $rootEntity = $this->pageRepository->findOneBy(['routeName' => PageInterface::PAGE_ROUTE_CMS_NAME ], ['id' => 'ASC']);
            foreach($this->categories as $idx => $cat){
                $entity = $this->pageRepository->findOneBy(['routeName' => $cat['route_name'], 'parent' => $rootEntity->getId()]);
//                $url = (empty($entity->getCustomUrl()))? $entity->getSlug(): $entity->getCustomUrl();
                $url = $entity->getSlug();
                $this->categories[$idx]['slug'] = $url;
            }
        }
        
        private function resolveUrls(): void
        {
            foreach($this->urlList as $cat => $list){
                if(!isset($this->categories[$cat])) continue;
                foreach($list as $url){
                    $this->fromTo[$url] = $this->renderUrl($url, $cat);
                }
            }
        }
        
        private function renderUrl(string $url, string $category): string
        {
            $url   = rtrim($url, '/');
            $parts = explode('/', $url);
            $slug  = end($parts);
            
            return $this->siteRelativepath . str_replace('{slug}', $slug, $this->categories[$category]['slug']);
        }
        
        /**
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        public function crawlRootSiteMap(): void
        {
            $sitemapsUrls = $this->getXml($this->config->getUrl().'/sitemap.xml');

            foreach($sitemapsUrls as $sitemapsUrl){
                $type = $this->extractSitemapKey($sitemapsUrl);
                if(!isset($this->urlList[$type])){
                    $this->urlList[$type] = [];
                }
                $this->urlList[$type] += $this->getXml($sitemapsUrl);
            }
        }
        
        /**
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        public function getXml(string $url): array
        {
            $sitemapsUrls = [];
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                $xml  = $response->getContent();
                $sitemapsUrls = $this->parseSitemap($xml);
            }
            return $sitemapsUrls;
        }
        
        public function extractSitemapKey(string $url): ?string
        {
            $pattern = '/\/([\w\-_]+)-sitemap\.xml$/';
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
            
            return null;
        }
        
        private function parseSitemap(string $xmlContent)
        : array {
            $xml = simplexml_load_string($xmlContent);
            $urls = [];
            
            foreach ($xml->sitemap as $urlElement) {
                $urls[] = (string)$urlElement->loc;
            }
            
            foreach ($xml->url as $urlElement) {
                $url = (string)$urlElement->loc;
                $urls[] = str_replace($this->config->getUrl(), '', $url);
            }
            
            return $urls;
        }
        
    }