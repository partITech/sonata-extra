<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Partitech\SonataExtra\Admin\ArticleAdmin;
use Partitech\SonataExtra\Repository\ArticleRepository;
use Partitech\SonataExtra\Repository\ArticleRevisionRepository;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleRevisionsController extends CRUDController
{
    private ArticleRevisionRepository $articleRevisionRepository;

    private Pool $pool;
    private ArticleRepository $articleRepository;

    #[Required]
    public function autowireDependencies(
        ArticleRevisionRepository $articleRevisionRepository,
        ArticleRepository $articleRepository,
        Pool $pool
    ): void {
        $this->articleRevisionRepository = $articleRevisionRepository;
        $this->articleRepository = $articleRepository;
        $this->pool = $pool;
    }

    public function applyRevisionAction($id = null, $childId = null): RedirectResponse
    {
        $admin = $this->admin;

        $articleRevision = $admin->getObject($childId);

        $articleAdmin = $this->pool->getAdminByAdminCode(ArticleAdmin::class);
        $article = $articleAdmin->getObject($id);

        if (!$article || !$articleRevision) {
            $this->addFlash('error', 'Article ou révision non trouvée.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        // Update article content with revision
        $article->setContent($articleRevision->getContent());
        $article->setTitle($articleRevision->getTitle());
        $article->setAuthor($articleRevision->getAuthor());
        $article->setStatus($articleRevision->getStatus());
        $article->setSlug($articleRevision->getSlug());

        foreach ($articleRevision->getCategory() as $category) {
            $article->getCategory()->add($category);
        }
        foreach ($articleRevision->getTags() as $tag) {
            $article->getTags()->add($tag);
        }

        $this->articleRepository->save($article, true);

        $this->addFlash('success', 'Révision appliquée avec succès.');

        return new RedirectResponse($articleAdmin->generateUrl('edit', ['id' => $article->getId()]));
    }
}
