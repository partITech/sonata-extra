<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Partitech\SonataExtra\Routing\PageUrlGenerator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class TranslationEntityListener
{
    public function __construct(
        private SiteManagerInterface  $siteManager,
        private RequestStack          $requestStack,
        private PageUrlGenerator      $PageUrlGenerator,
        private SiteSelectorInterface $siteSelector,
        private ParameterBagInterface $parameterBag,
    )
    {
    }

    public function postLoad(object $args): void
    {

        $locales = [];
        $entity = $args->getObject();
        $objectManager = $args->getObjectManager();

        if (!$this->isTranslation($entity) && !$this->isPage($entity)) {
            return;
        }

        if (method_exists($entity, 'getBaseRouteName')) {
            $baseRouteName = $entity->getBaseRouteName();
        }


        $current_site = $this->siteSelector->retrieve();
        $sites = $this->siteManager->findBy(['enabled' => true]);

        foreach ($sites as $site) {
            $locales[$site->getId()] = $site->getDefinition();
            $site_array[$site->getId()]=$site;
        }

        // get all the translations related to this entity
        $translations = $objectManager->getRepository(get_class($entity))
            ->createQueryBuilder('e')
            ->where('e.translation_from_id = :id')
            // ->orWhere('e.id = :id')
            ->setParameter('id', $entity->getTranslationFromId())
            ->getQuery()
            ->getResult();
        //check if has a default one
        $defaultTranslation=false;
        foreach ($translations as $translation) {
            if(method_exists($translation, 'getIsDefault') && $translation->getIsDefault()){
                $defaultTranslation=$translation;

            }
        }



        foreach ($translations as $translation) {

            if (!empty($translation->getSite())) {
                $locales[$translation->getSite()->getId()]['entity_id'] = $translation->getId();

                if (!empty($baseRouteName) && !$this->isPage($entity)) {

                    foreach ($baseRouteName as $route_name) {

                        $routeVariables = $this->PageUrlGenerator->createRouteVariableValue($translation, $route_name);
                        $routeVariables = array_merge($routeVariables, ['site' => $translation->getSite(), 'currentSite' => $current_site]);
                        try{
                            $url = $this->PageUrlGenerator->generate($route_name, $routeVariables, UrlGeneratorInterface::ABSOLUTE_URL);
                            $locales[$translation->getSite()->getId()]['routes'][$route_name] = $url;
                        }catch (\Exception $exception){ }
                    }
                } else {
                    if ($this->isPage($entity) && $entity->getRouteName() == 'page_slug' /*&& empty($baseRouteName)*/) {
                            $url = $this->PageUrlGenerator->generate($entity->getRouteName(), ['url' => $translation->getUrl(), 'site' => $translation->getSite(), 'currentSite' => $current_site], UrlGeneratorInterface::ABSOLUTE_URL);
                            $locales[$translation->getSite()->getId()]['routes'][$entity->getRouteName()] = $url;
                        /*if ($entity->getRouteName() == 'page_slug') {}*/
                    }else{
                        foreach ($baseRouteName as $route_name) {

                            $routeVariables = $this->PageUrlGenerator->createRouteVariableValue($translation, $route_name);
                            $routeVariables = array_merge($routeVariables, ['site' => $translation->getSite(), 'currentSite' => $current_site]);
                            try{
                                $url = $this->PageUrlGenerator->generate($route_name, $routeVariables, UrlGeneratorInterface::ABSOLUTE_URL);
                                $locales[$translation->getSite()->getId()]['routes'][$route_name] = $url;
                            }catch (\Exception $exception){ }
                        }
                    }

                }
            }
        }

        if($defaultTranslation){

            foreach($locales as $siteId=>$l){
                if(empty($l['entity_id']))
                {
                    $locales[$siteId]['entity_id']=$defaultTranslation->getId();
                    if (!empty($baseRouteName)) {
                        foreach ($baseRouteName as $route_name) {

                            $routeVariables = $this->PageUrlGenerator->createRouteVariableValue($translation, $route_name);
                            $routeVariables = array_merge($routeVariables, ['site' => $site_array[$siteId], 'currentSite' => $current_site]);
                            try{
                                $url = $this->PageUrlGenerator->generate($route_name, $routeVariables, UrlGeneratorInterface::ABSOLUTE_URL);
                                $locales[$siteId]['routes'][$route_name] = $url;
                            }catch (\Exception $exception){}
                        }
                    }
                }
            }
        }


        $entity->setTranslations($locales);

    }


    private function isTranslation(object $entity): bool
    {

        if (in_array(EntityTranslationTrait::class, class_uses($entity)) ) {
            return true;
        }

        return false;
    }

    private function isPage($entity):bool
    {
        $pageClass = ltrim($this->parameterBag->get('sonata.page.page.class'), '\\');
        if (is_a($entity, $pageClass)) {
            return true;
        }
        return false;
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $objectManager = $args->getObjectManager();

        // Needs to be a translation Trait Entity
       /* if (!$this->isTranslation($entity) || !$this->isPage($entity)) {
            return;
        }*/

        // Only for root entries


        if($this->isTranslation($entity)){
            if (!empty($entity->getTranslationFromId())) {
                return;
            }
            if (null !== $this->requestStack) {
                $request = $this->requestStack->getCurrentRequest();
                if (null !== $request) {
                    $siteId = $request->request->get('site_id');
                    if (!empty($siteId)) {
                        $site = $this->siteManager->find($siteId);
                    } else {
                        $site = $this->siteManager->findOneBy(['isDefault' => true]);
                    }

                    $entity->setSite($site);
                }
            }

            $entity->setTranslationFromId($entity->getId());
            $objectManager->persist($entity);
            $objectManager->getUnitOfWork()->computeChangeSets();

        }elseif($this->isPage($entity) && empty($entity->getTranslationFromId())){

            $entity->setTranslationFromId($entity->getId());
            $objectManager->persist($entity);
            $objectManager->getUnitOfWork()->computeChangeSets();

        }

    }

}
