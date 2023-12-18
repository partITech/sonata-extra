<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Enum\EditorStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

#[AsAdmin(
    manager_type: 'orm',
    label: 'Editor Revision',
    model_class: \Partitech\SonataExtra\Entity\EditorRevision::class,
    controller: \Partitech\SonataExtra\Controller\Admin\EditorRevisionsController::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]

)]
class EditorRevisionAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'editor-revision';
    protected $exportFormats = [];

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PAGE] = 1;
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    public function configureExportFields(): array
    {
        return [];
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('apply_revision', $this->getRouterIdParameter().'/apply-revision');
        $collection->remove('delete');
        $collection->remove('export');
        if ($this->isChild()) {
            return;
        }

        $collection->remove('create');

        $collection->remove('edit');

        // This is the route configuration as a parent
        $collection->clear();
    }

    public function configure(): void
    {
        $this->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/editor/edit.html.twig',
            'list' => '@PartitechSonataExtra/Admin/editor/list_revision.html.twig',
            'show' => '@PartitechSonataExtra/Admin/editor/show.html.twig',
        ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('content', null, ['label' => 'Revision Content'])
            ->add('revisionDate', null, ['label' => 'Created At']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('createdAt');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'header_style' => 'width: 50px;',
                'route' => ['name' => 'edit']
            ])
            ->add('status', null, [
                'template' => '@PartitechSonataExtra/Admin/editor/editor_status_list_field.html.twig',
                'status_enum' => EditorStatus::class,
                'header_style' => 'width: 100px;',
            ])
            ->add('featured_image', null, [
                'header_style' => 'flex-grow: 1;',
                'template' => '@PartitechSonataExtra/Admin/editor/featured_image_list_field.html.twig',
            ])

            ->add('title', null, [
                'header_style' => 'flex-grow: 1;',
            ])

            ->add('author', null, [
                /* 'header_style' => 'width: 150px;', */
            ])
            ->add('revisionDate', null, [
               /* 'header_style' => 'width: 180px;', */
            ])
            ->add('publishedAt', null, [
                /* 'header_style' => 'width: 180px;', */
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'apply' => [
                        'template' => '@PartitechSonataExtra/Admin/editor/editor_revision_list_action_apply.html.twig',
                    ],
                ],
                'header_style' => 'width: 210px;',
                'batch_actions' => false,
            ]);
    }
}
