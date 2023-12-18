<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Partitech\SonataExtra\Repository\ArticleRepository;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

use \Partitech\SonataExtra\Controller\Admin\TranslationController;

class ArticleController extends CRUDController
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;
    private ArticleRepository $articleRepository;

    private Pool $pool;
    private $TranslationController;
    #[Required]
    public function autowireDependencies(
        ArticleRepository $articleRepository,
        TranslatorInterface $translator,
        Pool $pool,
        TranslationController $TranslationController
    ): void {
        $this->articleRepository = $articleRepository;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->TranslationController = $TranslationController;
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn)
    {
        return $this->TranslationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
    }

    public function seoProposalAction(Request $request, $id)
    {

        $admin = $this->admin;
        $object = $admin->getObject($id);
        $locale=$object->getSite()->getLocale();
        return $this->TranslationController->seoProposalAction($object->getContent(),$locale);
    }

    public function editorTypeAction(Request $request, $id = null, $type = null): RedirectResponse
    {
        $admin = $this->admin;
        $article = $admin->getObject($id);
        $type = $request->query->get('type', 'gutenberg');
        if (in_array($type, ['gutenberg', 'ckeditor', 'textarea', 'markdown'])) {
            $article->setTypeEditor($type);
            $msg = $this->translator->trans('sonata-extra.article_admin.msg_editor_change', [], 'PartitechSonataExtraBundle');

            $this->articleRepository->save($article, true);
            $this->addFlash('success', $msg);
        }

        return new RedirectResponse($admin->generateUrl('edit', ['id' => $article->getId()]).'?_tab=tab__2');
    }


}
