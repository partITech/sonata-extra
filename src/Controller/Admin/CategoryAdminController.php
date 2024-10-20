<?php

declare(strict_types=1);

namespace Partitech\SonataExtra\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataMenu\Model\MenuInterface;
use Partitech\SonataMenu\Model\MenuItem;
use Partitech\SonataMenu\Model\MenuItemInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CategoryAdminController extends Controller
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;

    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private $translator;
    private $routeGenerator;
    private $adminPool;
    private $requestStack;
    private $TranslationController;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        DefaultRouteGenerator $routeGenerator,
        Pool $adminPool,
        RequestStack $requestStack,
        SiteManagerInterface $siteManager,
        ParameterBagInterface $parameterBag,
        TranslationController $TranslationController
    ): void {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->routeGenerator = $routeGenerator;
        $this->adminPool = $adminPool;
        $this->requestStack = $requestStack;
        $this->siteManager = $siteManager;
        $this->parameterBag = $parameterBag;
        $this->TranslationController = $TranslationController;
    }

    public static function getSubscribedServices(): array
    {
        return [
            'sonata.classification.manager.category' => CategoryManagerInterface::class,
            'sonata.classification.manager.context' => ContextManagerInterface::class,
        ] + parent::getSubscribedServices();
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn): Response
    {
        return $this->TranslationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
    }

    public function listAction(Request $request): Response
    {
        $this->assertObjectExists($request);

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if (!$request->query->has('filter') && !$request->query->has('filters')) {
            return new RedirectResponse($this->admin->generateUrl('tree', $request->query->all()));
        }

        $listMode = $request->query->get('_list_mode');
        if (\is_string($listMode)) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $datagridValues = $datagrid->getValues();

        $datagridContextIsSet = isset($datagridValues['context']['value']) && '' !== $datagridValues['context']['value'];

        // ignore `context` persistent parameter if datagrid `context` value is set
        if ('' !== $this->admin->getPersistentParameter('context', '') && !$datagridContextIsSet) {
            $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        if ($this->container->has('sonata.admin.admin_exporter')) {
            $exporter = $this->container->get('sonata.admin.admin_exporter');
            \assert($exporter instanceof AdminExporter);
            $exportFormats = $exporter->getAvailableFormats($this->admin);
        }

        return $this->renderWithExtraParams($this->admin->getTemplateRegistry()->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $exportFormats ?? $this->admin->getExportFormats(),
        ]);
    }

    public function treeAction(Request $request): Response
    {
        $categoryManager = $this->container->get('sonata.classification.manager.category');
        \assert($categoryManager instanceof CategoryManagerInterface);

        $currentContext = null;

        $contextId = $request->query->get('context');
        if (null !== $contextId) {
            $contextManager = $this->container->get('sonata.classification.manager.context');
            \assert($contextManager instanceof ContextManagerInterface);

            $currentContext = $contextManager->find($contextId);
        }

        if (null !== $request->get('btn_update') && 'POST' == $request->getMethod()) {
            $items = $request->get('items', null);
            $siteId = $request->get('siteid', null);

            if (!empty($items)) {
                $items = json_decode($items);
                $update = $this->updateCategoryTree($items);
                /* @var TranslatorInterface $translator */
                // $translator = $this->get('translator');

                $request->getSession()->getFlashBag()->add('notice',
                    $this->translator->trans(
                        $update ? 'sonata-extra.category.label_saved' : 'sonata-extra.category.label_error',
                        [],
                        'PartitechSonataExtraBundle'
                    )
                );

                return new RedirectResponse($this->routeGenerator->generateUrl(
                    $this->adminPool->getAdminByAdminCode('Partitech\SonataExtra\Admin\CategoryAdmin'),
                    'tree',
                    ['siteid' => $siteId]
                )
                );
            }
        }

        $this->updateCategoriesWithDefaultContext();

        // all root categories.
        $rootCategoriesSplitByContexts = $categoryManager->getRootCategoriesSplitByContexts(false);

        // root categories inside the current context
        $currentCategories = [];

        if (null === $currentContext && [] !== $rootCategoriesSplitByContexts) {
            $currentCategories = current($rootCategoriesSplitByContexts);
            \assert([] !== $currentCategories);
            $currentContext = current($currentCategories)->getContext();
        } elseif (null !== $currentContext) {
            foreach ($rootCategoriesSplitByContexts as $id => $contextCategories) {
                if ($currentContext->getId() !== $id) {
                    continue;
                }

                foreach ($contextCategories as $category) {
                    $catContext = $category->getContext();
                    if (null === $catContext || $currentContext->getId() !== $catContext->getId()) {
                        continue;
                    }

                    $currentCategories[] = $category;
                }
            }
        }

        $currentCategories = $this->getAllRootCategories(false);

        $datagrid = $this->admin->getDatagrid();

        if ('' !== $this->admin->getPersistentParameter('context', '')) {
            $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context', ''));
        }

        $formView = $datagrid->getForm()->createView();

        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplateRegistry()->getTemplate('tree'), [
            'action' => 'tree',
            'current_categories' => $currentCategories,
            'root_categories' => $rootCategoriesSplitByContexts,
            'current_context' => $currentContext,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    private function updateCategoryTree($items, $parent = null)
    {
        if (!empty($items)) {
            $categoryManager = $this->container->get('sonata.classification.manager.category');

            foreach ($items as $pos => $item) {
                $categoryItem = $categoryManager->findOneBy(['id' => $item->id]);
                if ($categoryItem) {
                    // dd($categoryItem);
                    $categoryItem->setPosition($pos);
                    $categoryItem->setParent($parent);

                    $this->entityManager->persist($categoryItem);
                }
                if (isset($item->children) && !empty($item->children)) {
                    $this->updateCategoryTree($item->children, $categoryItem);
                }
            }

            $this->entityManager->flush();

            $update = true;
        }

        return $update;
    }

    public function updateMenuTree($menu, $items, $parent = null)
    {
        $update = false;

        if (!($menu instanceof MenuInterface)) {
            $menu = $this->load($menu);
        }

        if (!empty($items) && $menu) {
            foreach ($items as $pos => $item) {
                /** @var MenuItem $menuItem */
                $menuItem = $this->menuItemRepository->findOneBy(['id' => $item->id, 'menu' => $menu]);

                if ($menuItem) {
                    $menuItem
                        ->setPosition($pos)
                        ->setParent($parent)
                    ;

                    $this->em->persist($menuItem);
                }

                if (isset($item->children) && !empty($item->children)) {
                    $this->updateMenuTree($menu, $item->children, $menuItem);
                }
            }

            $this->em->flush();

            $update = true;
        }

        return $update;
    }

    public function toggleAction($id)
    {
        /** @var MenuItemInterface $object */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $object->setEnabled(!$object->getEnabled());

        $m = $this->entityManager;
        $m->persist($object);
        $m->flush();

        return new RedirectResponse($this->routeGenerator->generateUrl(
            $this->adminPool->getAdminByAdminCode('Partitech\SonataExtra\Admin\CategoryAdmin'),
            'tree',
            ['site' => $object->getSite()->getId()]
        )
        );
    }

    public function getAllRootCategories(bool $loadChildren = true): array
    {
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request) {
                $siteId = $request->query->get('site');

                if (!empty($siteId)) {
                    $site = $this->siteManager->find($siteId);
                } else {
                    $site = $this->siteManager->findOneBy(['isDefault' => true]);
                }
                $this->siteId = $site->getId();
                $this->categories = [];
            }
        }

        $this->categories_class = $this->parameterBag->get('partitech_sonata_extra.category.class');
        /** @var CategoryInterface[] $rootCategories */
        $rootCategories = $this->entityManager->getRepository($this->categories_class)
            ->createQueryBuilder('c')
            ->where('c.parent IS NULL')
            ->andWhere('c.site = '.$this->siteId)
            ->getQuery()
            ->getResult();

        if ([] === $rootCategories) {
            return [];
        }

        $categories = [];

        foreach ($rootCategories as $category) {
            if (null === $category->getContext()) {
                throw new \LogicException(sprintf('Context of category "%s" cannot be null.', $category->getId() ?? ''));
            }

            $categories[] = $loadChildren ? $this->getRootCategoryWithChildren($category) : $category;
        }

        return $categories;
    }

    public function getRootCategoryWithChildren(CategoryInterface $category): CategoryInterface
    {
        $context = $category->getContext();
        if (null === $context) {
            throw new \InvalidArgumentException(sprintf('Context of category "%s" cannot be null.', $category->getId() ?? ''));
        }

        $contextId = $context->getId();
        if (null === $contextId) {
            throw new \InvalidArgumentException(sprintf('Context of category "%s" must have an not null identifier.', $category->getId() ?? ''));
        }

        if (null !== $category->getParent()) {
            throw new \InvalidArgumentException('Method can be called only for root categories.');
        }

        $this->loadCategories($context);

        foreach ($this->categories[$contextId] as $contextRootCategory) {
            if ($category->getId() === $contextRootCategory->getId()) {
                return $contextRootCategory;
            }
        }

        throw new \InvalidArgumentException(sprintf('Category "%s" does not exist.', $category->getId() ?? ''));
    }

    protected function loadCategories(ContextInterface $context): void
    {
        $contextId = $context->getId();
        if (null === $contextId || \array_key_exists($contextId, $this->categories)) {
            return;
        }

        /** @var CategoryInterface[] $categories */
        $categories = $this->entityManager->getRepository($this->categories_class)
            ->createQueryBuilder('c')
            ->where('c.context = :context')
            ->andWhere('c.site = :site') // Utilisez andWhere pour la seconde condition
            ->orderBy('c.parent', 'ASC') // Précisez l'ordre ASC ou DESC
            ->setParameters([
                'context' => $context, // Passez un tableau à setParameters
                'site' => $this->siteId,
            ])
            ->getQuery()
            ->getResult();

        if (0 === \count($categories)) {
            // no category, create one for the provided context
            $category = $this->create();
            $category->setName($context->getName());
            $category->setEnabled(true);
            $category->setContext($context);
            $category->setDescription($context->getName());

            $this->save($category);

            $categories = [$category];
        }

        $rootCategories = [];
        foreach ($categories as $category) {
            if (null === $category->getParent()) {
                $rootCategories[] = $category;
            }

            $categoryId = $category->getId();
            \assert(null !== $categoryId);
            $this->categories[$contextId][$categoryId] = $category;
        }

        $this->categories[$contextId] = $rootCategories;
    }

    private function updateCategoriesWithDefaultContext(): void
    {

        $this->categories_class = $this->parameterBag->get('partitech_sonata_extra.category.class');
        $categoryNoContext = $this->entityManager->getRepository($this->categories_class)
            ->createQueryBuilder('c')
            ->where('c.context IS NULL') // Vérifie que le contexte est NULL
            ->getQuery()
            ->getResult();

        if(!empty($categoryNoContext)){
            $this->context_class = $this->parameterBag->get('sonata.classification.admin.context.entity');
            $context = $this->entityManager->getRepository($this->context_class)
                ->createQueryBuilder('c')
                ->where('c.enabled = 1')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

            foreach($categoryNoContext as $category){
                $category->setContext($context);
                $this->entityManager->persist($category);
            }
            $this->entityManager->flush();


        }
    }
}
