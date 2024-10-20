<?php

namespace Partitech\SonataExtra\Traits;

use App\Entity\SonataPageSite;
use App\Repository\SonataPageSiteRepository;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;

#[ORM\HasLifecycleCallbacks]
trait AdminTranslationTrait
{
    private SonataPageSiteRepository $siteRepository;

    #[Required]
    public function autowireDependencies(
        SonataPageSiteRepository $sitesRepository,
        SiteManagerInterface $siteManager,
    ): void {
        $this->siteRepository = $sitesRepository;
        $this->siteManager = $siteManager;
    }
    private SiteManagerInterface $siteManager;
    private SonataPageSiteRepository $sitesRepository;
    private array $sites;

    protected function configure(): void
    {
        $this->configureTrait();
    }

    protected function configureTrait(): void
    {
        $this->sites = $this->siteManager->findAll();

        if ('sonata.admin.controller.crud' == $this->getBaseControllerName()) {
            $this->setBaseControllerName(\Partitech\SonataExtra\Controller\Admin\TranslationController::class);
        }

        $this->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/translation/edit.html.twig',
            'list' => '@PartitechSonataExtra/Admin/translation/list.html.twig',
            'inner_list_row' => '@PartitechSonataExtra/Admin/translation/inner_list_row.html.twig',
            'base_list_fields' => '@PartitechSonataExtra/Admin/translation/empty.html.twig',
        ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('createTranslation', $this->getRouterIdParameter().'/create-translation/{from_site}/{to_site}/{fqcn}');
        $collection->add('seoProposal', $this->getRouterIdParameter().'/seo-proposal/');
    }

    public function configureTraitRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('createTranslation', $this->getRouterIdParameter().'/create-translation/{from_site}/{to_site}/{fqcn}');
        $collection->add('seoProposal', $this->getRouterIdParameter().'/seo-proposal/');
    }

    public function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        return $this->configureQueryTrait($query);
    }

    public function configureQueryTrait(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $queryBuilder = $this->getModelManager()
                             ->createQuery($this->siteManager->getClass(), 's');

        $queryBuilder->where('s.isDefault = :default')
            ->setParameter('default', true);
        $site = $queryBuilder->getQuery()->getOneOrNullResult();

        $siteId = $this->getCurrentSelectedLocal($site->getId(), $site);

        $query->andWhere(
            $query->expr()->eq($query->getRootAliases()[0].'.site', ':site')
        )
                ->setParameter('site', $siteId);

        return $query;
    }

    public function getCreateTranslationRouteName(): string
    {
        return $this->getBaseRouteName().'_createTranslation';
    }

    public function getSeoProposalRouteName(): string
    {
        return $this->getBaseRouteName().'_seoProposal';
    }

    public function getEntityFqcn(): string
    {
        return $this->getClass();
    }

    public function getAdminCode(): string
    {
        return $this->getCode();
    }

    /** Accessor from template */
    public function getSiteList(): ?array
    {
        return $this->sites;
    }

    public function getCurrentSelectedLocal(mixed $default = false): ?int
    {

        $request = $this->getRequest();
        $site = $request->get('site', $default);

        if (null === $site) {
            $site = $request->request->get('site_id', $default);
        }

        return $site ?? $default;
    }

    public function getCurrentSite(): ?SonataPageSite
    {
        $currentSite = null;
        $currentSelectedLocal = $this->getCurrentSelectedLocal();
        foreach ($this->sites as $site) {
            if (null !== $currentSelectedLocal && $site->getId() === $currentSelectedLocal) {
                $currentSite = $site;
                break;
            } elseif (null === $currentSelectedLocal && $site->getIsDefault()) {
                $currentSite = $site;
                break;
            }
        }
        if (null === $currentSite && \count($this->sites) >= 1) {
            $currentSite = $this->sites[0];
        }

        return $currentSite;
    }

    public function toString(object $object): string
    {
        return (string) $this->getLabel();
    }

//    protected function preCreateTrait(Request $request, object $object): ?Response
//    {
//        $object->setSite($this->admin->getCurrentSite());
//
//        return null;
//    }
//
//    #[ORM\PrePersist]
//    protected function preCreate(Request $request, object $object): ?Response
//    {
//        dd('df');
//        return $this->preCreateTrait($request, $object);
//    }

    #[ORM\PrePersist]
    public function preCreate(PrePersistEventArgs $args): void
    {
        dd($args);
        $entity = $args->getObject();

        // Vérifie que l'objet est bien de l'entité attendue

        // Applique les modifications à l'entité avant la persistance
        $entity->setSite($this->getCurrentSite());
    }
}
