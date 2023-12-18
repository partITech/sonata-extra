<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Partitech\SonataExtra\Repository\EditorRepository;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;
use Partitech\SonataExtra\Controller\Admin\TranslationController;

class EditorController extends CRUDController
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;

    private EditorRepository $editorRepository;
    private Pool $pool;
    private $TranslationController;

    #[Required]
    public function autowireDependencies(
        EditorRepository $editorRepository,
        TranslatorInterface $translator,
        Pool $pool,
        TranslationController $TranslationController
    ): void {
        $this->editorRepository = $editorRepository;
        $this->pool = $pool;
        $this->translator = $translator;
        $this->TranslationController = $TranslationController;
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn)
    {
        return $this->TranslationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
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

        return new RedirectResponse($admin->generateUrl('edit', ['id' => $editor->getId()]).'?_tab=tab__2');
    }
}
