<?php

namespace Partitech\SonataExtra\Routing;

use InvalidArgumentException;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;


class PageUrlGenerator
{
    private RequestContext        $context;
    private RouterInterface       $router;
    private SiteSelectorInterface $siteSelector;
    private UrlGeneratorInterface $urlGenerator;
    private SiteManagerInterface  $siteManager;


    #[Required]
    public function required(
        RequestContext        $context,
        RouterInterface       $router,
        SiteSelectorInterface $siteSelector,
        UrlGeneratorInterface $urlGenerator,
        SiteManagerInterface  $siteManager,
    ): void
    {
        $this->context = $context;
        $this->router = $router;
        $this->siteSelector = $siteSelector;
        $this->urlGenerator = $urlGenerator;
        $this->siteManager  = $siteManager;
    }


    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {

        if (isset($parameters['currentSite'])) {
            $currentSite = $parameters['currentSite'];
            unset($parameters['currentSite']);
        } else {
            $currentSite = $this->siteSelector->retrieve();
        }
        $site = null;
        if (isset($parameters['site'])) {
            $site = $parameters['site'];
            unset($parameters['site']);
            $site_locale = $site->getLocale();

            if ($this->routeExists($name . '_' . $site_locale)) {
                $name = $name.'_' . $site_locale;
            }
        }

        if( is_null($currentSite) ){
            $currentSite = $this->siteManager->findOneBy(['isDefault' => true]);
        }

        $relativePath = $currentSite->getRelativePath();

        if ($name !== 'page_slug') {
            $url = $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        } else {
            $url = $relativePath . $parameters['url'];
        }

        if (str_starts_with($url, $relativePath)) {
            $url = substr($url, strlen($relativePath));
        }

        if ($referenceType === UrlGeneratorInterface::ABSOLUTE_PATH) {
            return $url;
        }

        $schemeAuthority = null;
        if (UrlGeneratorInterface::ABSOLUTE_URL === $referenceType || UrlGeneratorInterface::NETWORK_PATH === $referenceType) {
            $port = '';
            if ('http' === $this->context->getScheme() && 80 !== $this->context->getHttpPort()) {
                $port = sprintf(':%s', $this->context->getHttpPort());
            } elseif ('https' === $this->context->getScheme() && 443 !== $this->context->getHttpsPort()) {
                $port = sprintf(':%s', $this->context->getHttpsPort());
            }

            $schemeAuthority = UrlGeneratorInterface::NETWORK_PATH === $referenceType ? '//' : sprintf('%s://', $this->context->getScheme());
            $schemeAuthority = sprintf('%s%s%s', $schemeAuthority, $this->context->getHost(), $port);
        }
        // patch voir avec thomas pourquoi on va choper site.
        if(is_null($site) && $currentSite instanceof SiteInterface){
            $site = $currentSite;
        }

        return $schemeAuthority . $site->getRelativePath() . $url;
    }

    public function routeExists(string $routeName): bool
    {
        return $this->router->getRouteCollection()->get($routeName) !== null;
    }


    public function getRouteArguments(string $routeName): array
    {
        try{
            $route = $this->router->getRouteCollection()->get($routeName);
        }catch(\Exception $e){
            $route = false;
        }
        if(!empty($route)){
            return $route->compile()->getPathVariables();
        }else{
            return [];
        }
    }

    function createRouteVariableValue($entity, $routeName): array
    {
        $routeVariables = $this->getRouteArguments($routeName);
        $result = [];

        foreach ($routeVariables as $variable) {
            $getterMethod = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $variable)));

            if (!method_exists($entity, $getterMethod)) {
                throw new InvalidArgumentException("Getter method '{$getterMethod}' does not exist in the entity.");
            }
            $value = $entity->$getterMethod();
            $result[$variable] = $value;
        }

        return $result;
    }

    public function isCliMode(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}