<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\SmartService\SmartServiceProviderFactoryInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;



class PageAdminController extends AbstractController
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;

    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private Pool $adminPool;
    private ParameterBagInterface $parameterBag;
    private SmartServiceProviderFactoryInterface $smartServiceProviderFactory;
    private PageManager $PageManager;

    #[Required]
    public function autowireDependencies(
        TokenStorageInterface                $tokenStorage,
        EntityManagerInterface               $entityManager,
        TranslatorInterface                  $translator,
        Pool                                 $adminPool,
        ParameterBagInterface                $parameterBag,
        SmartServiceProviderFactoryInterface $smartServiceProviderFactory,
        SlugifyInterface                     $slugify,
        TemplateManagerInterface            $templateManager,
        RequestStack                        $requestStack,
        CmsManagerSelectorInterface $cmsSelector,
        PageServiceManagerInterface $pageServiceManager,
    ): void {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->adminPool = $adminPool;
        $this->parameterBag = $parameterBag;
        $this->templateManager = $templateManager;
        $this->smartServiceProviderFactory = $smartServiceProviderFactory;
        $this->slugify = $slugify;
        $this->requestStack = $requestStack;
        $this->smartServiceTranslation=$this->parameterBag->get('partitech_sonata_extra.smart_service.provider.translation');
        $this->cmsSelector = $cmsSelector;
        $this->pageServiceManager = $pageServiceManager;
    }

    #[Route('/admin/app/sonatapagepage/create-page-from-locale/{id}/{from_site}/{to_site}', name: 'sonata_extra_page_create_page_from_locale')]
    public function createPageFromLocaleAction($id, $from_site, $to_site): Response|bool
    {

        $smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');
        $blockClass = $this->parameterBag->get('sonata.page.block.class');
        $admin = $this->adminPool->getAdminByAdminCode('sonata.page.admin.page');
        $object = $admin->getObject($id);
        $translations=$object->getTranslations();
        if(!empty($translations[$from_site]) && !empty($translations[$from_site]['entity_id'])){
            $object = $admin->getObject($translations[$from_site]['entity_id']);
        }

        $parent = $object->getParent();

        if (!empty($parent)) {
            $parentObject = $admin->getObject($parent->getId());
            $translationId = $parentObject->getTranslationFromId();

            // get the parent from the cibling site.
            $pageClass = $this->parameterBag->get('sonata.page.page.class');
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('p')
                ->from($pageClass, 'p')
                ->where('p.site = :site')
                ->andWhere('p.translation_from_id = :translation_from_id')
                ->setParameter('translation_from_id', $translationId)
                ->setParameter('site', $to_site);
            $parent_id = $qb->getQuery()->getOneOrNullResult();
        }

        // get cibling site
        $siteClass = $this->parameterBag->get('sonata.page.site.class');
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from($siteClass, 's')
            ->where('s.id = :id')
            ->setParameter('id', $to_site);
        $site = $qb->getQuery()->getOneOrNullResult();

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find the object with id: %s', $id));
        }

        if($object->getRouteName()!='page_slug'){
            return true;
        }

        $className = get_class($object);
        $clonedObject = new $className();



        if (true === $smart_service_conf['translate_on_create_page']) {
            $translationProvider = $this->smartServiceProviderFactory->create($this->smartServiceTranslation);
            $targetLanguage = $site->getLocale();

            $translate_array = [];
            if(!empty(trim($object->getName()))){
                $translate_array['page_name'] = trim($object->getName());
            }
            if(!empty(trim($object->getName()))){
                $translate_array['title'] = trim($object->getName());
            }
            if(!empty(trim($object->getMetaKeyword()))){
                $translate_array['metaKeyword'] = trim($object->getMetaKeyword());
            }

            if(!empty(trim($object->getMetaDescription()))){
                $translate_array['metaDescription'] = trim($object->getMetaDescription());
            }

            if(!empty(trim($object->getOgTitle()))){
                $translate_array['ogTitle'] = trim($object->getOgTitle());
            }

            if(!empty(trim($object->getOgDescription()))){
                $translate_array['ogDescription'] = trim($object->getOgDescription());
            }


            $translation = $translationProvider->translateArray($translate_array, $targetLanguage);


            if(!empty(trim($object->getName()))){
                $clonedObject->setName($translation['page_name']['translated']);
            }
            if(!empty(trim($object->getName()))){
                $clonedObject->setTitle($translation['title']['translated']);
            }
            if(!empty(trim($object->getMetaKeyword()))){
                $clonedObject->setMetaKeyword($translation['metaKeyword']['translated']);
            }

            if(!empty(trim($object->getMetaDescription()))){
                $clonedObject->setMetaDescription($translation['metaDescription']['translated']);
            }

            if(!empty(trim($object->getOgTitle()))){
                $clonedObject->setOgTitle($translation['ogTitle']['translated']);
            }

            if(!empty(trim($object->getOgDescription()))){
                $clonedObject->setOgDescription($translation['ogDescription']['translated']);
            }

        }else{
            $clonedObject->setName($object->getName());
            $clonedObject->setTitle($object->getName());
            $clonedObject->setMetaKeyword($object->getMetaKeyword());
            $clonedObject->setMetaDescription($object->getMetaDescription());
            $clonedObject->setOgTitle($object->getOgTitle());
            $clonedObject->setOgDescription($object->getOgDescription());
        }

        $clonedObject->setType($object->getType());
        $clonedObject->setEnabled($object->getEnabled());
        $clonedObject->setJavascript($object->getJavascript());
        $clonedObject->setStylesheet($object->getStylesheet());
        $clonedObject->setTemplateCode($object->getTemplateCode());
        $clonedObject->setDecorate($object->getDecorate());
        $clonedObject->setPosition($object->getPosition());
        $clonedObject->setRequestMethod($object->getRequestMethod());
        $clonedObject->setEdited(false);
        $clonedObject->setCreatedAt(new \DateTime('now'));
        //$clonedObject->setId(null);
        $clonedObject->setUrl(null);
        $clonedObject->setSlug(null);
        $clonedObject->setSite($site);
        $clonedObject->setParent($parent_id);
        $clonedObject->setTranslationFromId($object->getTranslationFromId());


        //check if the object allready exist
        $pageClass = $this->parameterBag->get('sonata.page.page.class');
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from($pageClass, 'p')
            ->where('p.site = :site')
            ->andWhere('p.translation_from_id = :translationFromId')
            ->setParameter('site', $object->getSite())
            ->setParameter('translationFromId', $object->getTranslationFromId());
        $page = $qb->getQuery()->getOneOrNullResult();
        if($page){
            if (PHP_SAPI !== 'cli') {
                $editUrl = $admin->generateObjectUrl('edit', $page);
                return $this->redirect($editUrl . '?site=' . $site->getId());
            }else{
                return true;
            }
        }


        $this->entityManager->persist($clonedObject);
        $this->entityManager->flush();

        $clonedObject=$this->setUrl($clonedObject);
        $this->entityManager->persist($clonedObject);
        $this->entityManager->flush();


        foreach ($object->getBlocks() as $block) {
            $clonedBlock = new $blockClass;
            if(empty($block->hasParent())){
                $clonedBlock->setName($block->getName());
                $clonedBlock->setType($block->getType());
                $clonedBlock->setSettings($block->getSettings());
                $clonedBlock->setEnabled($block->getEnabled());
                $clonedBlock->setPosition($block->getPosition());
                $clonedBlock->setCreatedAt(new \DateTime());
                $clonedObject->addBlock($clonedBlock);
                $this->entityManager->persist($clonedObject);
                foreach ($block->getChildren() as $childBlock) {
                    $clonedChildBlock = new $blockClass;
                    if(empty($childBlock->getName())){
                        $clonedChildBlock->setName("");
                    }else{
                        $clonedChildBlock->setName($childBlock->getName());
                    }

                    $clonedChildBlock->setType($childBlock->getType());
                    $clonedChildBlock->setSettings($childBlock->getSettings());
                    $clonedChildBlock->setEnabled($childBlock->getEnabled());
                    $clonedChildBlock->setPosition($childBlock->getPosition());
                    $clonedChildBlock->setCreatedAt(new \DateTime());
                    $clonedChildBlock->setUpdatedAt(null);
                    $clonedBlock->addChild($clonedChildBlock);
                    $this->entityManager->persist($clonedChildBlock);

                }

            }

            $this->entityManager->persist($clonedObject);


        }

        $this->entityManager->persist($clonedObject);
        $this->entityManager->flush();



        if (PHP_SAPI !== 'cli') {
            $editUrl = $admin->generateObjectUrl('edit', $clonedObject);
            return $this->redirect($editUrl . '?site=' . $site->getId());
        }else{
            return true;
        }
    }

    public function setUrl($entity)
    {

        if ($entity->isInternal()) {
            $entity->setUrl(null); // internal routes do not have any url ...
        }

        // hybrid page cannot be altered
        if (!$entity->isHybrid()) {
            $parent = $entity->getParent();

            if (null !== $parent) {
                $slug = $entity->getSlug();

                if (null === $slug) {
                    $slug = $this->slugify->slugify($entity->getName() ?? '');

                    $entity->setSlug($slug);
                }

                $parentUrl = $parent->getUrl();

                if ('/' === $parentUrl) {
                    $base = '/';
                } elseif (!str_ends_with($parentUrl ?? '', '/')) {
                    $base = $parentUrl.'/';
                } else {
                    $base = $parentUrl;
                }

                $url = $entity->getCustomUrl() ?? $slug;
                $entity->setUrl('/'.ltrim($base.$url, '/'));
            } else {
                $entity->setSlug(null);

                $url = $entity->getCustomUrl() ?? '';
                $entity->setUrl('/'.ltrim($url, '/'));
            }
        }
        return $entity;
    }


    #[Route('/admin/app/sonatapagepage/seo-proposal/{id}/', name: 'sonata_extra_page_seo_proposal')]
    public function seoProposalAction($id): Response
    {
        $this->smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');

        if(true===$this->smart_service_conf['seo_proposal_on_page'])
        {
            $admin = $this->adminPool->getAdminByAdminCode('sonata.page.admin.page');
            $object = $admin->getObject($id);
            if (!$object) {
                throw $this->createNotFoundException('the object does not exist.');
            }

            $templateCode = $object->getTemplateCode();
            if (null === $templateCode) {
                return false;
            }

            $request = $this->requestStack->getCurrentRequest();

            $cms = $this->cmsSelector->retrieve();
            $cms->setCurrentPage($object);
            $current_render=$this->pageServiceManager->execute($object, $request);
            $content=$current_render->getContent();


            $seo_proposal_provider=$this->smart_service_conf['seo_provider'];
            $seoProposalProviderFactory = $this->smartServiceProviderFactory->create($seo_proposal_provider);
            $proposal = $seoProposalProviderFactory->getSeoProposal($content, $object->getSite()->getLocale());
            return new JsonResponse($proposal);
        }
        return new Response();
    }

    #[Route('/admin/app/sonatapagepage/preview/{id}/', name: 'sonata_extra_page_preview')]
    public function previewAction($id): Response
    {
        $realtime_preview = $this->parameterBag->get('partitech_sonata_extra.page.realtime_preview');

        if(true===$realtime_preview)
        {
            $admin = $this->adminPool->getAdminByAdminCode('sonata.page.admin.page');
            $object = $admin->getObject($id);
            if (!$object) {
                throw $this->createNotFoundException('the object does not exist.');
            }

            $templateCode = $object->getTemplateCode();
            if (null === $templateCode) {
                return false;
            }

            $request = $this->requestStack->getCurrentRequest();

            $cms = $this->cmsSelector->retrieve();
            $cms->setCurrentPage($object);
            $current_render=$this->pageServiceManager->execute($object, $request);
            $content=$current_render->getContent();

            return new Response($content);
        }
        return new Response();
    }
}
