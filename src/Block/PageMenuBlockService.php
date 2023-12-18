<?php

// src/Block/HelloWorldBlockService.php

namespace Partitech\SonataExtra\Block;

use App\Entity\SonataPagePage;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class PageMenuBlockService extends AbstractBlockService implements EditableBlockService
{
    private $entityManager;
    private ParameterBagInterface $parameterBag;
    private CmsManagerSelectorInterface $cmsSelector;

    #[Required]
    public function autowireDependencies(
        Environment $twig,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        CmsManagerSelectorInterface $cmsSelector,
    ): void {
        parent::__construct($twig);
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->cmsSelector = $cmsSelector;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $cms = $this->cmsSelector->retrieve();
        $site = $cms->getCurrentPage()->getSite();

        $settings = $blockContext->getSettings();
        $template = $settings['template'];
        $routeNames = $settings['route_names'];
        $selectedPageId = $settings['selected_page'];

        if ($selectedPageId) {
            $pages = $this->entityManager->getRepository(SonataPagePage::class)
                ->findBy(['routeName' => $routeNames, 'site' => $site]);
        } else {
            $pages = $this->entityManager->getRepository(SonataPagePage::class)
                ->findBy(['routeName' => $routeNames, 'site' => $site]);
        }

        $tree = $this->buildPageTree($pages, $selectedPageId);

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'tree' => $tree,
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $cms = $this->cmsSelector->retrieve();

        $routeNames = $this->entityManager->createQueryBuilder()
            ->select('p.routeName')
            ->from(SonataPagePage::class, 'p')
            ->distinct()
            ->getQuery()
            ->getArrayResult();

        $routeChoices = array_combine(array_column($routeNames, 'routeName'), array_column($routeNames, 'routeName'));

        $pageNames = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(SonataPagePage::class, 'p')
            ->getQuery()
            ->getResult();

        $pageNamesChoices = [];
        $pageNamesChoices[''] = null;
        foreach ($pageNames as $pageNameArray) {
            $pageName = $pageNameArray->getName();
            $id = $pageNameArray->getId();
            $site = $pageNameArray->getSite()->getName();

            $pageNamesChoices[$site.' # '.$id.' # '.$pageName] = $id;
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['route_names', ChoiceType::class, [
                    'label' => 'Route Names',
                    'translation_domain' => 'SonataExtraBundle',
                    'choices' => $routeChoices,
                    'multiple' => true,
                    'expanded' => true,
                ]],
                ['selected_page', ChoiceType::class, [
                    'label' => 'Page de départ',
                    'translation_domain' => 'SonataExtraBundle',
                    'choices' => $pageNamesChoices,
                    'multiple' => false,
                    'expanded' => false,
                ]],

                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                ]],
            ],
            'translation_domain' => 'SonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_names' => [],
            'selected_page' => null,
            'class' => null,
            'template' => '@PartitechSonataExtra/Blocks/page/menu_default.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Menu page', null, null, 'SonataBlockBundle', [
            'class' => 'icon-block-tree_view',
        ]);
    }

    private function buildPageTree(array $pages, $selectedPageId = false): array
    {
        // Indexer les pages par ID pour un accès facile
        $indexedPages = [];
        foreach ($pages as $page) {
            $indexedPages[$page->getId()] = ['page' => $page, 'children' => []];
        }

        // Si un ID de page sélectionnée est fourni, filtrer les pages
        if (false !== $selectedPageId) {
            $indexedPages = $this->filterDescendants($indexedPages, $selectedPageId);
        }

        // Construire l'arbre
        $tree = [];
        foreach ($indexedPages as $id => $node) {
            $parent = $node['page']->getParent();

            if (!empty($parent)) {
                $parentId = $parent->getId();
            } else {
                $parentId = null;
            }

            if (null == $parentId || 0 == $parentId || !isset($indexedPages[$parentId])) {
                $tree[] = &$indexedPages[$id];
            } else {
                $indexedPages[$parentId]['children'][] = &$indexedPages[$id];
            }
        }

        return $tree;
    }

    // Fonction supplémentaire pour filtrer les descendants de la page sélectionnée
    private function filterDescendants(array $indexedPages, $selectedPageId): array
    {
        $descendants = [];
        foreach ($indexedPages as $id => $node) {
            $parent = $node['page']->getParent();
            while (null !== $parent) {
                if ($parent->getId() == $selectedPageId) {
                    $descendants[$id] = $node;
                    break;
                }
                $parent = $parent->getParent();
            }
        }

        return $descendants;
    }
}
