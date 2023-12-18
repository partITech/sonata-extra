<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Traits\ControllerTranslationTrait;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;;
use Partitech\SonataExtra\SmartService\SmartServiceProviderFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;
use Partitech\SonataExtra\Service\TranslateObjectService;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationController extends CRUDController
{
    use ControllerTranslationTrait;


    private TranslateObjectService $TranslateObjectService;
    private Pool $adminPool;
    private $entityManager;
    private $parameterBag;
    private SmartServiceProviderFactoryInterface $smartServiceProviderFactory;

    #[Required]
    public function autowireDependencies(
        TranslateObjectService $TranslateObjectService,
        Pool $adminPool,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        SmartServiceProviderFactoryInterface $smartServiceProviderFactory,
    ): void {
        $this->TranslateObjectService = $TranslateObjectService;
        $this->adminPool = $adminPool;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->smartServiceProviderFactory = $smartServiceProviderFactory;

    }

    #[Route('/admin/SonataExtra/create-translation/{id}/{from_site}/{to_site}/{fqcn}', name: 'sonata_extra_translation_create_page_from_locale')]
    public function createTranslationAction($id, $from_site, $to_site, $fqcn): Response
    {
        $admin = $this->adminPool->getAdminByAdminCode($fqcn);
        $clonedObject = $this->TranslateObjectService->createTranslation($id, $from_site, $to_site, $fqcn);

        $siteClass = $this->parameterBag->get('sonata.page.site.class');
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from($siteClass, 's')
            ->where('s.id = :id')
            ->setParameter('id', $to_site);
        $site = $qb->getQuery()->getOneOrNullResult();
        $this->site = $site;

        $editUrl = $admin->generateObjectUrl('edit', $clonedObject);
        return $this->redirect($editUrl.'?site='.$this->site->getId());
    }

    #[Route('/admin/SonataExtra/seo-proposal/', name: 'sonata_extra_seo_proposal')]
    public function seoProposalAction($content, $locale): Response
    {
        $this->smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');

        $proposal=false;
        if(true===$this->smart_service_conf['seo_proposal_on_article'])
        {
            $seo_proposal_provider=$this->smart_service_conf['seo_provider'];
            $seoProposalProviderFactory = $this->smartServiceProviderFactory->create($seo_proposal_provider);
            $proposal = $seoProposalProviderFactory->getSeoProposal($content, $locale);
        }
        return new JsonResponse($proposal);
    }
}
