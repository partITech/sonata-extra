<?php

namespace Partitech\SonataExtra\Service;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\Asset\Packages;
use MatthiasMullie\Minify;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AssetsHandler
{
    private $css = [];
    private $cssInline = [];
    private $js = [];
    private $jsInline = [];
    private $packages;
    private $cache;
    private $parameterBag;
    #[Required]
    public function autowireDependencies(
        Packages $packages,
        CacheInterface $cache,
        ParameterBagInterface $parameterBag
    ):void{
        $this->packages = $packages;
        $this->cache = $cache;
        $this->parameterBag = $parameterBag;
    }

    // Méthodes pour ajouter des ressources
    public function addCss($path, $index = 'default')
    {
        $this->css[$index][] = $path;
    }

    public function addCssInline($content, $index = 'default')
    {
        $this->cssInline[$index][] = $content;
    }

    public function addJs($path, $defer = false, $index = 'default')
    {
        $this->js[$index][] = ['path' => $path, 'defer' => $defer];
    }

    public function addJsInline($content, $index = 'default')
    {
        $this->jsInline[$index][] = $content;
    }

    // Méthodes pour obtenir les ressources
    public function getCss($index)
    {
        return $this->css[$index] ?? [];
    }

    public function getCssInline($index)
    {
        return $this->cssInline[$index] ?? [];
    }

    public function getJs($index)
    {
        return $this->js[$index] ?? [];
    }

    public function getJsInline($index)
    {
        return $this->jsInline[$index] ?? [];
    }

    public function getBlocksCss($index)
    {

        $output = '';
        foreach ($this->css[$index] ?? [] as $css) {
            $output .= sprintf('<link href="%s" rel="stylesheet">', $css);
        }

        return $output;
    }

    public function getBlocksCssInline($index, $compress = false)
    {
        $output = '';
        foreach ($this->cssInline[$index] ?? [] as $cssInline) {
            $output .= sprintf('<style>%s</style>', $cssInline);
        }



        if ($compress) {
            $cacheKey = md5($output);
            $cachedContent = $this->getFromCache($cacheKey);
            if ($cachedContent === null) {
                $minifier = new Minify\CSS();
                $minifier->add($output);
                $output = $minifier->minify();
                $this->saveToCache($cacheKey, $output);
            } else {
                $output = $cachedContent;
            }

        }
        return $output;
    }

    public function getBlocksJs($index)
    {
        $output = '';
        foreach ($this->js[$index] ?? [] as $js) {
            $output .= sprintf('<script src="%s" %s></script>', $js['path'], $js['defer'] ? 'defer' : '');
        }
        return $output;
    }

    public function getBlocksJsInline($index, $compress=false)
    {

        $output = '';
        foreach ($this->jsInline[$index] ?? [] as $jsInline) {
            $output .= sprintf('<script>%s</script>', $jsInline);
        }

        if ($compress) {
            $cacheKey = md5($output);
            if ($this->shouldMinify()) {
                $cachedContent = $this->getFromCache($cacheKey);
                if ($cachedContent === null) {
                    $minifier = new Minify\JS();
                    $minifier->add($output);
                    $output = $minifier->minify();

                    $this->saveToCache($cacheKey, $output);
                } else {
                    $output = $cachedContent;
                }
            }
        }

        return $output;
    }

    private function shouldMinify(): bool
    {
        $environment = $this->parameterBag->get('kernel.environment');
        return $environment === 'prod';
    }

    private function getFromCache(string $cacheKey)
    {
        return $this->cache->get($cacheKey, function ($item) {
            return null;
        });
    }

    private function saveToCache(string $cacheKey, string $content)
    {
        $this->cache->get($cacheKey, function ($item) use ($content) {
            $item->expiresAfter(3600*24); // TTL  1 hour *24
            return $content;
        });
    }
}