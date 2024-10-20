<?php

namespace Partitech\SonataExtra\Controller\Admin;

use App\Form\ImportCsvType;
use Partitech\SonataExtra\Service\ImportCsvHandler;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectionAdminController extends CRUDController
{
    private ImportCsvHandler $importCsvHandler;
    private TranslatorInterface $translator;

    #[Required]
    public function autowireDependencies(
        ImportCsvHandler $importCsvHandler,
        TranslatorInterface $translator
    ): void {
        $this->importCsvHandler = $importCsvHandler;
        $this->translator = $translator;
    }

    public function importAction(Request $request): Response|RedirectResponse
    {
        $form = $this->createForm(ImportCsvType::class, null, [
            'action' => $this->generateUrl('admin_partitech_sonataextra_redirection_import'),
            'method' => 'POST',
            'attr' => ['id' => 'import-form'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $this->importCsvHandler->handle($file);
            $msg_success = $this->translator->trans('sonata-extra.redirect_admin.success.import', [], 'PartitechSonataExtraBundle');
            $this->addFlash('sonata_flash_success', $msg_success);

            return $this->redirectToRoute('admin_partitech_sonataextra_redirection_list');
        }

        return $this->render('@PartitechSonataExtra/Admin/redirection/import_csv.html.twig', [
            'form' => $form->createView(),
            'action' => 'import',
            'object' => null,
            'objectId' => null,
        ]);
    }
}
