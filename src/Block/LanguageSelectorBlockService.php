<?php

namespace Partitech\SonataExtra\Block;

use _PHPStan_993c0a2e7\Nette\Neon\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Listener\RequestListener;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;
use Partitech\SonataExtra\Service\AssetsHandler;
use Symfony\Component\HttpFoundation\RequestStack;

use Sonata\PageBundle\CmsManager\CmsManagerInterface;

use function PHPUnit\Framework\throwException;


#[AutoconfigureTag(name: 'sonata.block')]
class LanguageSelectorBlockService extends AbstractBlockService
{
    private $pageManager;
    private $router;
    private CmsManagerSelectorInterface $cmsSelector;
    private AssetsHandler $assetsHandler;
    private CmsManagerInterface $cmsManager;

    #[Required]
    public function autowireDependencies(
        Environment $twig,
        FactoryInterface $factory,
        CmsManagerSelectorInterface $cmsSelector,
        RequestListener $requestListener,
        PageManagerInterface $pageManager,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        MediaManager $mediaManager,
        ImageProvider $providerImage,
        RouterInterface $router,
        AssetsHandler $assetsHandler,
        RequestStack $requestStack,
        CmsManagerInterface $cmsManager
    ): void {
        $this->requestListener = $requestListener;
        $this->pageManager = $pageManager;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->mediaManager = $mediaManager;
        $this->providerImage = $providerImage;
        $this->router = $router;
        $this->cmsSelector = $cmsSelector;
        $this->assetsHandler = $assetsHandler;
        $this->requestStack = $requestStack;
        $this->cmsManager = $cmsManager;
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@PartitechSonataExtra/Blocks/page/language_selector.html.twig',
            'style_url' => '',
        ]);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $page = $this->getCurrentPage();
        $translations = $page->getTranslations();
        $settings = $blockContext->getSettings();
        $style_url = $settings['style_url'];
        if(!empty($style_url)){
            $this->assetsHandler->addCss($style_url);
        }

        // We primary check if we have values in current request.
        // Any process could inject the values into the current request
        // to manipulate the selector.
        $request = $this->requestStack->getCurrentRequest();
        $requestLinks = $request->attributes->get('Language-Selector');
        $requestCurrent = $request->attributes->get('Language-Selector-Current');
        if (!empty($_GET)) {
            $queryString = http_build_query($_GET);
            $queryString = '?' . $queryString;
        }else{
            $queryString='';
        }

        if(!empty($requestLinks) && !empty($requestCurrent)){


            $translations=[];
            foreach($requestLinks as $l){
                $translations[$l['site']]['routes'][$page->getRouteName()] = $l['routes'][0];
                $translations[$l['site']]['label']=$l['label'];
                $translations[$l['site']]['lang']=$l['lang'];
                $translations[$l['site']]['routes'][0]=$l['routes'][0].$queryString;
                if($l['site']==$page->getSite()->getId()){
                    $current=$l;
                }

            }
            if(empty($current)){
                $current=$requestCurrent;
            }

        }else{

            if($page->getRouteName()==='page_slug' || $page->getRouteName()==='sonata_extra_blog_search')
            {


                $current=false;
                foreach ($translations as $site => $t) {
                    if (!empty($t['entity_id']) && $t['entity_id'] == $page->getId()) {
                        $current = $t;
                    }
                    if (!empty($t['entity_id'])) {
                        $translation_page = $this->pageManager->findOneBy(['id' => $t['entity_id']]);
                        $url = '//'.$translation_page->getSite()->getHost().$translation_page->getSite()->getRelativePath().$translation_page->getUrl();
                        if ($translation_page->getEnabled()) {
                            $translations[$site]['url'] = $url;
                        }
                        if( !empty($translations[$site]['routes'][$page->getRouteName()])){
                            $translations[$site]['routes'][$page->getRouteName()].=$queryString;
                        }

                    }
                }
            }
        }


        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'translations' => $translations,
            'current' => $current,
            'style_url' => '',
            'page' => $page,
        ], $response);
    }

    private function getCurrentPage(): ?PageInterface
    {
        $cms = $this->cmsSelector->retrieve();
        $pageClass = $this->parameterBag->get('sonata.page.page.class');
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from($pageClass, 'p')
            ->where('p.id = :id')
            ->setParameter('id', $cms->getCurrentPage()->getId());

        return $qb->getQuery()->getOneOrNullResult();

        //return $cms->getCurrentPage();
    }

    public function parseLinkHeader(string $linkHeader): array
    {
        $links = [];
        $parts = explode(',', $linkHeader);
        foreach ($parts as $part) {
            if (preg_match('/<([^>]+)>;\s*rel="alternate";\s*hreflang="([^"]+)"/', $part, $matches)) {
                $links[] = [
                    'url' => $matches[1],
                    'lang' => $matches[2]
                ];
            }
        }
        return $links;
    }
}
