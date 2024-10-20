<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use Exception;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Config;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Import
{
    private string $url;
    private string $apiUser;
    private string $apiToken;
    
    public function __construct(
        private readonly Config $config,
        private readonly MediasSync $mediasSync,
        private readonly TagsSync $tagsSync,
        private readonly CategoriesSync $categoriesSync,
        private readonly UserSync $userSync,
        private readonly PostSync $postSync,
        private readonly PagesSync $pageSync,
        private readonly HtaccessRedirections $htaccessRedirections,
        #[Autowire('%kernel.project_dir%/var/')]
        private readonly string $projectDir
    ) {
    }
    
    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function execute(): void
    {
        $this->config->setUrl($this->url)->setUser($this->apiUser)->setToken($this->apiToken);

        $this->mediasSync->sync();
        $this->userSync->sync();
        $this->categoriesSync->sync();
        $this->tagsSync->sync();
        $this->postSync->sync();
        $this->pageSync->sync();
        
        file_put_contents(
            $this->projectDir.'htaccess',
            $this->htaccessRedirections->create()->getHtaccess()
        );
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Import
    {
        $this->url = $url;
        return $this;
    }
    
    public function setApiUser(string $apiUser): Import
    {
        $this->apiUser = $apiUser;
        return $this;
    }

    public function setApiToken(string $apiToken): Import
    {
        $this->apiToken = $apiToken;
        return $this;
    }
}
