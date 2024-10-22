<?php

namespace Partitech\SonataExtra\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleService
{
    private ParameterBagInterface $parameterBag;
    private EntityManagerInterface $entityManager;
    private ?Request $request;

    #[Required]
    public function required(
        ParameterBagInterface $parameterBag,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ): void {
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();

    }
    public static function getCodeByLocal(string $local): string
    {
        return substr($local, 0, 2);
    }

    public function fixEntityCategory($entity){
        $categoryClass = $this->parameterBag->get('partitech_sonata_extra.category.class');


        $site_id=$entity->getSite()->getId();
        $categories = $entity->getCategory();
        $r = $this->entityManager->getRepository($categoryClass);
        foreach($categories as $c){
            if(!empty($c->translations[$site_id]['entity_id'])){
                $entity->removeCategory($c);
                $q = $r->createQueryBuilder('q');
                $q->where('q.id = :id');
                $q->setParameter('id', $c->translations[$site_id]['entity_id']);
                $rs = $q->getQuery()->getOneOrNullResult();
                $entity->addCategory($rs);
            }
        }
        return $entity;
    }

    public function fixEntityTag(mixed $entity): mixed
    {
        $tagClass = $this->parameterBag->get('partitech_sonata_extra.tag.class');
        $site_id=$entity->getSite()->getId();
        $tags = $entity->getTags();
        $r = $this->entityManager->getRepository($tagClass);
        $rs=[];
        foreach($tags as $c){
            if(!empty($c->translations[$site_id]['entity_id'])){
                $entity->removeTag($c);
                $q = $r->createQueryBuilder('q');
                $q->where('q.id = :id');
                $q->setParameter('id', $c->translations[$site_id]['entity_id']);
                $rs = $q->getQuery()->getOneOrNullResult();
                $entity->addTag($rs);
            }
        }
        return $entity;
    }

    public function fixEntityCategoryTag($entity){
        $entity=$this->fixEntityCategory($entity);
        $entity=$this->fixEntityTag($entity);
        return $entity;
    }


    public function fixMenuItemUrl($menuItem){
        $current_locale=$menuItem->getMenu()->getSite()->getId();
        //try to find a page with this url
        $pageClass = $this->parameterBag->get('sonata.page.page.class');
        $pageRepository = $this->entityManager->getRepository($pageClass);
        $pageItem=$pageRepository->findOneBy(['url'=>$menuItem->getUrl()]);
        if(!empty($pageItem)){
            $pageItemSite=$pageItem->getSite()->getId();

            //check if the url is from the current site. if not, try to build new one.
            if($pageItemSite!=$current_locale){
                //take the url from the localized page
                $translations=$pageItem->getTranslations();
                if(!empty($translations[$current_locale]) && !empty($translations[$current_locale]['entity_id']) )
                {
                    //Grab the localized page URl and set it into the menuItem.
                    $loalizedPageItem=$pageRepository->findOneBy(['id'=>$translations[$current_locale]['entity_id']]);
                    $url=$loalizedPageItem->getUrl();
                    if(!empty($url)){
                        $menuItem->setUrl($url);
                        $this->entityManager->persist($menuItem);
                        $this->entityManager->flush();
                        return true;
                    }
                }
            }
            return false;
        }else {
            return false;
        }
    }

    public function languageSelectorSetLinks($entity,$routeName){
        $linkHeaderParts = [];
        $languageSelector = [];
        foreach ($entity->translations as $site=>$translation) {
            if (isset($translation['lang']) && isset($translation['routes'][$routeName])) {
                $lang = $translation['lang'];
                $url = $translation['routes'][$routeName];
                $linkHeaderParts[] = "<$url>; rel=\"alternate\"; hreflang=\"$lang\"";
                $languageSelector[$site]=[
                    "lang"=>$lang,
                    "label"=>$translation['label'],
                    "entity_id"=>$translation['entity_id'],
                    "site"=>$translation['site'],
                    "lang"=>$lang,
                    "routes"=>[$url]
                ];

            }
        }
        $linkHeader = implode(', ', $linkHeaderParts);

        $this->request->attributes->set('Language-Selector', $languageSelector);
        $this->request->attributes->set('Language-Selector-Current', $entity->translations[$entity->getSite()->getId()]);

        return $linkHeader;
    }


    public function languageSelectorGetHeaderLinks($entity,$routeName){
        return $this->languageSelectorSetLinks($entity,$routeName);
    }

    public function languageSelectorGetLinks(){
        $this->request->attributes->get('Language-Selector');
    }

    public function languageSelectorGetCurrent(){
        $this->request->attributes->get('Language-Selector-Current');
    }
}
