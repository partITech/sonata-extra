<?php

namespace Partitech\SonataExtra\Admin\Extension;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Partitech\SonataExtra\Contract\MediaInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
// use Sonata\AdminBundle\Form\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\HttpFoundation\RequestStack;

class PageAdminExtension extends AbstractAdminExtension
{
    private EntityManagerInterface $entityManager;
    private MediaManager $mediaManager;
    private ImageProvider $providerImage;
    private ParameterBagInterface $parameterBag;
    private bool $seoProposal = false;
    private array $sites = [];

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        MediaManager $mediaManager,
        ImageProvider $providerImage,
        RequestStack $requestStack,
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->mediaManager = $mediaManager;
        $this->providerImage = $providerImage;


        $request = $requestStack->getCurrentRequest();
        if(!empty($request)){
            $smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');
            $real_time_preview = $this->parameterBag->get('partitech_sonata_extra.page.realtime_preview');
            $request->attributes->set('seo_proposal_on_page' , $smart_service_conf['seo_proposal_on_page']);
            $request->attributes->set('realtime_preview' , $real_time_preview);
        }


    }

    /*
     * This should be removed as it is not used anymore.
     */
    public function getSeoProposalEnabled(){

        return $this->seoProposal;
    }

    public function cleanLocalRouteNames()
    {
        $pageClass = $this->parameterBag->get('sonata.page.page.class');
        $qb = $this->entityManager->createQueryBuilder();

        $pages = $qb->select('p')
            ->from($pageClass, 'p')
            ->where('p.pageAlias IS NOT NULL')
            ->andWhere($qb->expr()->like('p.pageAlias', ':pageAlias'))
            ->setParameter('pageAlias', '_page_alias_%')
            ->getQuery()
            ->getResult();

        $routeNamesToDelete = [];
        foreach ($pages as $page) {
            $routeName = str_replace('_page_alias_', '', $page->getPageAlias());
            foreach($this->sites as $site) {
                $routeNamesToDelete[$routeName.'_'.$site->getLocale()] = "'".$routeName.'_'.$site->getLocale()."'";
            }
        }

        if(!empty($routeNamesToDelete)){
            $placeholdersString = implode(', ', $routeNamesToDelete);
            $qb = $this->entityManager->createQueryBuilder();
            $qb->delete($pageClass, 'p')
                ->where('p.pageAlias IS NULL')
                ->andWhere('p.routeName IN (' . $placeholdersString . ')')
                ->getQuery()
                ->execute();
        }


    }
    public function configure(AdminInterface $admin): void
    {
        $siteClass = $this->parameterBag->get('sonata.page.site.class');

        $qb = $this->entityManager->createQueryBuilder();

        try{
            $this->sites = $qb->select('s')->from($siteClass, 's')->getQuery()->getResult();
        }catch(\Throwable $e){
            $this->sites = [];
        }

        if (count($this->sites)) {
            $admin->setTemplates([
                'edit' => '@PartitechSonataExtra/Admin/page/custom_edit.html.twig',
                'compose' => '@PartitechSonataExtra/Admin/page/custom_edit.html.twig',
                'action' => '@PartitechSonataExtra/Admin/page/empty.html.twig',
            ]);
        }
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void
    {

    }

    public function configureListFields(ListMapper $list): void
    {
        $this->cleanLocalRouteNames();
    }

    public function configureFormFields(FormMapper $form): void
    {
        $admin = $form->getAdmin();
        $page = $admin->hasSubject() ? $admin->getSubject() : null;
        $mediaUrl = null;
        $mediaName = null;
        if ($page && null !== $page->getOgImage()) {
            $media = $this->mediaManager->findOneBy([
                'id' => $admin->getSubject()->getOgImage()->getId(),
            ]);
            if ($media instanceof MediaInterface) {
                $mediaUrl = $this->providerImage->generatePublicUrl($media, 'default_small');
                $mediaName = $media->getName();
            }
        }

        $imageHtml = $mediaUrl ? '<img src="'.$mediaUrl.'" alt="'.$mediaName.'" class="img-thumbnail" />' : 'Aucune image sélectionnée';

        if ($page->isHybrid()) {
            $form->with('main')
                ->add('customUrl', TextType::class, [
                    'required' => false,
                    'label' => 'Route personnalisée',
                ])
                ->end();
        }

        $form
            ->with('seo')
            ->add('ogTitle', null, [
                'label' => 'og:title',
            ])
            ->add('ogDescription', null, [
                'label' => 'og:description',
            ])
            ->add('ogImage', ModelListType::class, [
                'label' => 'og:image',
                'required' => false,
                'btn_add' => true,
                'btn_edit' => true,
                'btn_list' => false,
                'btn_delete' => false,
                'class' => "App\Entity\SonataMediaMedia",
                'help' => $imageHtml,
                'help_html' => true,
            ],
                ['link_parameters' => [
                    'context' => 'default',
                    'provider' => 'sonata.media.provider.image',
                ]]
            )
            ->end();
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, $childAdmin = null): void
    {
        if ('edit' == $action) {
            $page = $admin->getSubject();
            if (!$page->isHybrid() && !$page->isInternal()) {
                try {
                    $path = $page->getUrl();
                    $site = $page->getSite();

                    if (null !== $site) {
                        $relativePath = $site->getRelativePath();

                        if (!\in_array($relativePath, [null, '/'], true)) {
                            $path = $relativePath.$path;
                        }
                    }

                    $menu->addChild('view_page', [
                        'uri' => '//'.$site->getHost().$path,
                        'linkAttributes' => ['target' => '_blank'],
                    ]);
                } catch (\Exception) {
                    // avoid crashing the admin if the route is not setup correctly
                    // throw $e;
                }
            }
        }
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
        $page = $admin->getSubject();
        if ($page->isHybrid() && !empty($object->getCustomUrl()) && empty($object->getPageAlias())) {
            $route_initial=$object->getRouteName();
            $route_new=$route_initial.'_'.$object->getSite()->getLocale();

            $object->setRouteName($route_new);
            $object->setSlug('');
            $object->setPageAlias($route_initial);
        }

    }
}
