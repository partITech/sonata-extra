<?php

namespace Partitech\SonataExtra\Traits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ControllerTranslationTrait
{
    public function listAction(Request $request): Response
    {
        // empty creation button, as we want client to clic on a flag
        $this->admin->setTemplates(['button_create' => '@PartitechSonataExtra/Admin/translation/empty.html.twig']);

        return parent::listAction($request);
    }

    protected function preCreateTrait(Request $request, object $object): ?Response
    {

        $object->setSite($this->admin->getCurrentSite());

        return null;
    }

    protected function preCreate(Request $request, object $object): ?Response
    {
        return $this->preCreateTrait($request, $object);
    }
}
