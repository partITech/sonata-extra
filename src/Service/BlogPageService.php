<?php

namespace Partitech\SonataExtra\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Sonata\PageBundle\Page\TemplateManager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsService]
#[Autoconfigure(tags: ['sonata.page'])]
class BlogPageService implements PageServiceInterface
{
    private TemplateManager $templateManager;

    private $parameterBag;

    #[Required]
    public function required(
        KernelInterface $kernel,
        MediaManagerInterface $mediaManager,
        EntityManagerInterface $entityManager,
        Pool $providerPool,
        ParameterBagInterface $parameterBag,
        TemplateManager $templateManager
    ): void {
        $this->configDir = $kernel->getProjectDir().'/config';
        $this->mediaManager = $mediaManager;
        $this->entityManager = $entityManager;
        $this->providerPool = $providerPool;
        $this->parameterBag = $parameterBag;
        $this->templateManager = $templateManager;
    }

    public function getName(): string
    {
        return 'Blog page';
    }

    public function execute(PageInterface $page, Request $request, array $parameters = [], Response $response = null): Response
    {
        $seoParameters = [
            'ogTitle' => $response->headers->get('X-Custom-Header-ogTitle'),
            'ogDescription' => $response->headers->get('X-Custom-Header-ogDescription'),
            'ogImage' => $response->headers->get('X-Custom-Header-ogImage'),
            'seoTitle' => $response->headers->get('X-Custom-Header-seoTitle'),
            'seoKeyword' => $response->headers->get('X-Custom-Header-seoKeyword'),
            'seoDescription' => $response->headers->get('X-Custom-Header-seoDescription'),
        ];
        $parameters = array_merge($parameters, $seoParameters);

        return $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);
    }
}
