<?php
    
    namespace Partitech\SonataExtra\Service\ImportWordpress\Api;
    
    use Exception;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    use Throwable;
    
    class Api
    {
        
        protected string $url;
        private array $datas = [];
        protected bool $auth = false;
        protected string $endPoint = '';

        public function __construct(
            private readonly Config $config,
            protected readonly HttpClientInterface $client,
            protected EventDispatcherInterface $eventDispatcher,
            protected readonly LoggerInterface $logger,
        ) {
        }
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        public function get(string $url): ?string
        {
            $content = null;
            try {
                $response = $this->client->request(
                    'GET',
                    $url
                );
                if (401 === $response->getStatusCode()) {
                    $this->logger->error(sprintf('Failed to process Api request  "%s" : %s', $url , 'Unauthorized'));
                    return null;
                }
                if (404 === $response->getStatusCode()) {
                    return null;
                }
                $content = $response->getContent();
            } catch (TransportExceptionInterface $e) {
                $this->logger->error(sprintf('Failed to process Api request  "%s" : %s', $url , $e->getMessage()));
            }
            
            
            return $content;
        }
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         * @throws Exception
         */
        public function getById(int $id): ? array
        {
            $content =  $this->get(url :  $this->config->getUrl() . $this->endPoint . '/' . $id);
            
            $infos = json_decode($content, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new Exception('Error decoding JSON');
            }
            
            return $infos;
        }
        
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         * @throws Exception
         * @throws TransportExceptionInterface
         */
        public function getPage(int $page): ?array
        {
            $datas = null;
            $response = null;
            $params = [
                'query' => [
                    'per_page' => Config::PER_PAGE,
                    'page' => $page,
                ],
            ];
            
            if($this->auth){
                $params['auth_basic'] = [
                    $this->config->getUser(),
                    $this->config->getToken()
                ];
            }
            
            try {
                $response = $this->client->request(
                    'GET',
                    $this->config->getUrl() . $this->endPoint,
                    $params
                );
                $content = $response->getContent();
                $datas = json_decode($content, true);
            } catch (Throwable $e) {
                $this->logger->error(sprintf('Failed to process Api request  "%s", page %d: %s', $this->config->getUrl() . $this->endPoint, $page , $e->getMessage()));
            }
            
            if (401 === $response->getStatusCode()) {
                $this->logger->error(sprintf('Failed to process Api request  "%s" : %s', $this->endPoint , 'Unauthorized'));
                return null;
            }
            
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new Exception('Error decoding JSON');
            }
            
            return $datas;
        }
        
        /**
         * @throws ServerExceptionInterface|TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface
         */
        public function getAll(): array
        {
            $page = 1;
            
            while (true) {

                $pageContent = $this->getPage($page);
                
                if (empty($pageContent)) {
                    break;
                }
                
                $this->datas = array_merge($this->datas, $pageContent);
                
                ++$page;
            }
            
            return $this->datas;
        }
        public function setUrl(string $url): self
        {
            $this->url = $url;
            
            return $this;
        }

    }