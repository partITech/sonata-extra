<?php

namespace Partitech\SonataExtra\Controller\Admin;

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

    #[Required]
    public function autowireDependencies(
        ArticleRevisionRepository $articleRevisionRepository,
        ArticleRepository $articleRepository,
        TranslatorInterface $translator,
        Pool $pool
    ): void {
        $this->articleRevisionRepository = $articleRevisionRepository;
        $this->articleRepository = $articleRepository;
        $this->pool = $pool;
    }

    public function applyRevisionAction(Request $request, $id = null, $childId = null): RedirectResponse
    {
        $admin = $this->admin;

        $articleRevision = $admin->getObject($childId);

        $articleAdmin = $this->pool->getAdminByAdminCode(\Partitech\SonataExtra\Admin\ArticleAdmin::class);
        $article = $articleAdmin->getObject($id);

        if (!$article || !$articleRevision) {
            $this->addFlash('error', 'Article ou révision non trouvée.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        // Mettre à jour le contenu de l'article avec la révision
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
