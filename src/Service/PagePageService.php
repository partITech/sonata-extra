<?php

namespace Partitech\SonataExtra\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Sonata\PageBundle\Page\TemplateManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

class PagePageService implements PageServiceInterface
{
    private $parameterBag;
    private TemplateManager $templateManager;

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
        return "Partitech\SonataExtra\Service\PagePageService";
    }

    public function execute(PageInterface $page, Request $request, array $parameters = [], Response $response = null): Response
    {
        // add custom processing (load data, update SEO values, update http headers, perform security checks, ...)

        return $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);
    }
}
