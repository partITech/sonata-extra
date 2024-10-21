<?php

namespace Partitech\SonataExtra\Service;


use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\PageServiceInterface;
use Sonata\PageBundle\Page\TemplateManager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

#[Autoconfigure(tags: ['sonata.page'])]
class BlogPageService implements PageServiceInterface
{
    private TemplateManager $templateManager;

    #[Required]
    public function required(
        TemplateManager $templateManager
    ): void {
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
