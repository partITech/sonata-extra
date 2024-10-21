<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Partitech\SonataExtra\Repository\EditorRepository;
use Partitech\SonataExtra\Traits\ControllerTranslationTrait;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditorController extends CRUDController
{
    use ControllerTranslationTrait;

    private EditorRepository $editorRepository;
    private Pool $pool;
    private TranslationController $translationController;
    private TranslatorInterface $translator;

    #[Required]
    public function autowireDependencies(
        EditorRepository      $editorRepository,
        TranslatorInterface   $translator,
        Pool                  $pool,
        TranslationController $translationController
    ): void
    {
        $this->editorRepository = $editorRepository;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->translationController = $translationController;
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn): Response
    {
        return $this->translationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
    }

    public function editorTypeAction(Request $request, $id = null, $type = null): RedirectResponse
    {
        $admin = $this->admin;
        $editor = $admin->getObject($id);
        $type = $request->query->get('type', 'gutenberg');
        if (in_array($type, ['gutenberg', 'ckeditor', 'textarea', 'markdown'])) {
            $editor->setTypeEditor($type);
            $msg = $this->translator->trans('sonata-extra.editor_admin.msg_editor_change', [], 'PartitechSonataExtraBundle');

            $this->editorRepository->save($editor, true);
            $this->addFlash('success', $msg);
        }

        return new RedirectResponse($admin->generateUrl('edit', ['id' => $editor->getId()]) . '?_tab=tab__2');
    }
}
