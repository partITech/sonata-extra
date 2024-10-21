<?php

namespace Partitech\SonataExtra\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Partitech\SonataExtra\Entity\Article;
use Partitech\SonataExtra\Service\LocaleService;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;

use Sonata\PageBundle\Site\SiteSelectorInterface;

class BlogController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private PaginatorInterface $paginator;
    private Pool $mediaPool;
    private SiteSelectorInterface $siteSelector;
    private RouterInterface $router;
    private LocaleService $localeService;


    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameterBag,
        PaginatorInterface     $paginator,
        Pool                   $mediaPool,
        SiteSelectorInterface  $siteSelector,
        RouterInterface        $router,
        LocaleService          $localeService,
    ): void
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->paginator = $paginator;
        $this->mediaPool = $mediaPool;
        $this->siteSelector = $siteSelector;
        $this->router = $router;
        $this->localeService = $localeService;
    }

    #[Route('/blog-category/{slug}', name: 'sonata_extra_blog_category')]
    public function category(string $slug, Request $request): Response
    {
        $category = $slug;
        $pageNumber = $request->query->getInt('page', 1);
        $settings = $this->parameterBag->get('partitech_sonata_extra.blog');
        $max_per_page = $settings['category']['max_per_page'];
        $category_class = $this->parameterBag->get('partitech_sonata_extra.category.class');

        $categoryEntity = $this->entityManager->getRepository($category_class)->findOneBy(['slug' => $category, 'enabled' => true]);
        if (!$categoryEntity) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $articles = $this->entityManager->getRepository(Article::class)
            ->QueryPublishedByCategory($categoryEntity, $this->siteSelector->retrieve());

        $pagination = $this->paginator->paginate(
            $articles,
            $pageNumber,
            $max_per_page,

        );

        $response = $this->render('@PartitechSonataExtra/Controller/Blog/category.html.twig', [
            'category' => $category,
            'pagination' => $pagination,
            'entity' => $categoryEntity
        ]);

        /* inject translations links into language request for language selector*/
        $linkHeader = $this->localeService->languageSelectorGetHeaderLinks($categoryEntity, 'sonata_extra_blog_category');
        $response->headers->set('Link', $linkHeader);

        if (method_exists($categoryEntity, 'getSeoOgTitle')) {
            $response->headers->set('X-Custom-Header-ogTitle', $categoryEntity->getSeoOgTitle());
        }
        if (method_exists($categoryEntity, 'getSeoOgDescription')) {
            $response->headers->set('X-Custom-Header-ogDescription', $categoryEntity->getSeoOgDescription());
        }
        if (method_exists($categoryEntity, 'getSeoOgImage')) {
            $response->headers->set('X-Custom-Header-ogImage', $this->getMediaPath($categoryEntity->getSeoOgImage()));
        }
        if (method_exists($categoryEntity, 'getSeoTitle')) {
            $response->headers->set('X-Custom-Header-seoTitle', $categoryEntity->getSeoTitle());
        }
        if (method_exists($categoryEntity, 'getSeoKeywords')) {
            $response->headers->set('X-Custom-Header-seoKeyword', $categoryEntity->getSeoKeywords());
        }
        if (method_exists($categoryEntity, 'getSeoDescription')) {
            $response->headers->set('X-Custom-Header-seoDescription', $categoryEntity->getSeoDescription());
        }

        return $response;
    }


    #[Route('/blog-tag/{slug}', name: 'sonata_extra_blog_tag')]
    public function tag(string $slug, Request $request): Response
    {
        $pageNumber = $request->query->getInt('page', 1);
        $settings = $this->parameterBag->get('partitech_sonata_extra.blog');
        $max_per_page = $settings['category']['max_per_page'];
        $tag_class = $this->parameterBag->get('partitech_sonata_extra.tag.class');

        $tagEntity = $this->entityManager->getRepository($tag_class)->findOneBy(['slug' => $slug, 'enabled' => true]);
        if (!$tagEntity) {
            throw $this->createNotFoundException('Le tag demandée n\'existe pas.');
        }

        $articles = $this->entityManager->getRepository(Article::class)
            ->QueryPublishedByTag($tagEntity);

        $pagination = $this->paginator->paginate(
            $articles,
            $pageNumber,
            $max_per_page
        );

        $response = $this->render('@PartitechSonataExtra/Controller/Blog/tag.html.twig', [
            'tag' => $tagEntity,
            'pagination' => $pagination,
        ]);

        /* inject translations links into language request for language selector*/
        $linkHeader = $this->localeService->languageSelectorGetHeaderLinks($tagEntity, 'sonata_extra_blog_tag');
        $response->headers->set('Link', $linkHeader);

        if (method_exists($tagEntity, 'getSeoOgTitle')) {
            $response->headers->set('X-Custom-Header-ogTitle', $tagEntity->getSeoOgTitle());
        }
        if (method_exists($tagEntity, 'getSeoOgDescription')) {
            $response->headers->set('X-Custom-Header-ogDescription', $tagEntity->getSeoOgDescription());
        }
        if (method_exists($tagEntity, 'getSeoOgImage')) {
            $response->headers->set('X-Custom-Header-ogImage', $this->getMediaPath($tagEntity->getSeoOgImage()));
        }
        if (method_exists($tagEntity, 'getSeoTitle')) {
            $response->headers->set('X-Custom-Header-seoTitle', $tagEntity->getSeoTitle());
        }
        if (method_exists($tagEntity, 'getSeoKeywords')) {
            $response->headers->set('X-Custom-Header-seoKeyword', $tagEntity->getSeoKeywords());
        }
        if (method_exists($tagEntity, 'getSeoDescription')) {
            $response->headers->set('X-Custom-Header-seoDescription', $tagEntity->getSeoDescription());
        }

        return $response;
    }

    #[Route('/blog-article/{slug}', name: 'sonata_extra_blog_article')]
    public function article(string $slug, Request $request): Response
    {

        $content = $this->entityManager->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (!$content) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $response = $this->render('@PartitechSonataExtra/Controller/Blog/article.html.twig', [
            'article' => $content,
        ]);

        /* inject translations links into language request for language selector*/
        $linkHeader = $this->localeService->languageSelectorGetHeaderLinks($content, 'sonata_extra_blog_article');
        $response->headers->set('Link', $linkHeader);

        $response->headers->set('Content-Language', $content->translations[$content->getSite()->getId()]['lang']);
        $response->headers->set('X-Custom-Header-ogTitle', $content->getSeoOgTitle());
        $response->headers->set('X-Custom-Header-ogDescription', $content->getSeoOgDescription());
        $response->headers->set('X-Custom-Header-ogImage', $this->getMediaPath($content->getSeoOgImage()));
        $response->headers->set('X-Custom-Header-seoTitle', $content->getSeoTitle());
        $response->headers->set('X-Custom-Header-seoKeyword', $content->getSeoKeywords());
        $response->headers->set('X-Custom-Header-seoDescription', $content->getSeoDescription());

        return $response;
    }

    #[Route('/blog-search', name: 'sonata_extra_blog_search')]
    public function search(Request $request): Response
    {
        $searchTerm = $request->query->get('s');
        $pageNumber = $request->query->getInt('page', 1);
        $settings = $this->parameterBag->get('partitech_sonata_extra.blog');
        $max_per_page = $settings['category']['max_per_page'];

        $articles = $this->entityManager->getRepository(Article::class)
            ->QueryPublishedByKeyWord($searchTerm, $this->siteSelector->retrieve());

        $pagination = $this->paginator->paginate(
            $articles,
            $pageNumber,
            $max_per_page,

        );

        return $this->render('@PartitechSonataExtra/Controller/Blog/search.html.twig', [
            'category' => null,
            'pagination' => $pagination,
            'entity' => null
        ]);
    }

    private function getMediaPath($media = false): false|string
    {
        if (empty($media)) {
            return false;
        }

        $provider = $this->mediaPool->getProvider($media->getProviderName());
        $formatExists = array_key_exists('og_image', $provider->getFormats());
        $resolvedFormat = $formatExists ? 'og_image' : 'reference';
        return $provider->generatePublicUrl($media, $resolvedFormat);
    }
}
