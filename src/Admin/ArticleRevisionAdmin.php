<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Controller\Admin\ArticleRevisionsController;
use Partitech\SonataExtra\Entity\ArticleRevision;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

#[AsAdmin(
    manager_type: 'orm',
    label: 'Article Revision',
    model_class: ArticleRevision::class,
    controller: ArticleRevisionsController::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class ArticleRevisionAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'article-revision';
    protected array $exportFormats = [];

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
            'edit' => '@PartitechSonataExtra/Admin/article/edit.html.twig',
            'list' => '@PartitechSonataExtra/Admin/article/list_revision.html.twig',
            'show' => '@PartitechSonataExtra/Admin/article/show.html.twig',
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
                'template' => '@PartitechSonataExtra/Admin/article/article_status_list_field.html.twig',
                'status_enum' => ArticleStatus::class,
                'header_style' => 'width: 100px;',
            ])
            ->add('featured_image', null, [
                'header_style' => 'flex-grow: 1;',
                'template' => '@PartitechSonataExtra/Admin/article/featured_image_list_field.html.twig',
            ])
            ->add('slug', null, [
                'header_style' => 'flex-grow: 1;',
            ])
            ->add('title', null, [
                'header_style' => 'flex-grow: 1;',
            ])
            ->add('category', null, ['label' => 'Categories'])
            ->add('tags', null, ['label' => 'Tags'])
            ->add('seo_title')
            ->add('seo_keywords')
            ->add('seo_description')
            ->add('seo_og_title')
            ->add('seo_og_description')
            ->add('seo_og_image', null, [
                'header_style' => 'flex-grow: 1;',
                'template' => '@PartitechSonataExtra/Admin/article/ogimage_image_list_field.html.twig',
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
                        'template' => '@PartitechSonataExtra/Admin/article/article_revision_list_action_apply.html.twig',
                    ],
                ],
                'header_style' => 'width: 210px;',
                'batch_actions' => false,
            ]);
    }
}
