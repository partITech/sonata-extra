<?php

namespace Partitech\SonataExtra\Twig;

use Partitech\SonataExtra\Routing\PageUrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Partitech\SonataExtra\Service\AssetsHandler;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class Extension extends AbstractExtension
{
    private $assetsHandler;
    private $packages;

    public function __construct(private readonly PageUrlGenerator $pageUrlGenerator)
    {
    }
    
    #[Required]
    public function autowireDependencies(
        AssetsHandler $assetsHandler,
        Packages $packages
    ):void{
        $this->assetsHandler=$assetsHandler;

    }
    public function getFilters()
    {
        return [
            new TwigFilter('remove_br', [$this, 'removeBr']),
            new TwigFilter('get_class', [$this, 'getClass']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sonata_extra_get_blocks_css', [$this, 'getBlocksCss']),
            new TwigFunction('sonata_extra_get_blocks_css_inline', [$this, 'getBlocksCssInline']),
            new TwigFunction('sonata_extra_get_blocks_js', [$this, 'getBlocksJs']),
            new TwigFunction('sonata_extra_get_blocks_js_inline', [$this, 'getBlocksJsInline']),
            new TwigFunction('generate_url_locale', [$this, 'generateUrlLocale']),
        ];
    }

    public function removeBr(string $string): string
    {
        return str_replace('<br>', '', $string);
    }


    public function getBlocksCss($index)
    {
        return $this->assetsHandler->getBlocksCss($index);
    }

    public function getBlocksCssInline($index, $compress = false)
    {
        return $this->assetsHandler->getBlocksCssInline($index, $compress);
    }

    public function getBlocksJs($index)
    {
        return $this->assetsHandler->getBlocksJs($index);
    }

    public function getBlocksJsInline($index, $compress = false)
    {
        return $this->assetsHandler->getBlocksJsInline($index, $compress);
    }
    
    public function generateUrlLocale(string $routeName, array $routeVariables = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        return $this->pageUrlGenerator->generate($routeName, $routeVariables, $referenceType);
    }
    public function getClass($object)
    {
        return get_class($object);
    }
}
