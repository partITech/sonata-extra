<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\FormatterBundle\Form\Type\SimpleFormatterType;

#[AsAdmin(
    manager_type: 'orm',
    label: 'FAQs - Questions',
    model_class: \Partitech\SonataExtra\Entity\FaqQuestion::class,
    parent: \Partitech\SonataExtra\Admin\FaqCategoryAdmin::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class FaqQuestionAdmin extends AbstractAdmin
{
    use SortableAdminTrait {
        configureRoutes as protected sortableAdminTraitConfigureRoutes;
    }
    protected $parentAssociationMapping = 'category';

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('category', ModelType::class)
            ->add('active')
            ->add('question')
            ->add('answer', SimpleFormatterType::class, [
                'format' => 'richhtml',
                'ckeditor_context' => 'default',
                'ckeditor_image_format' => 'big',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('active')
            ->add('question')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('active')
            ->addIdentifier('question')
            ->add('category.name')
            ->add('_action', 'actions', [
                'actions' => [
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->sortableAdminTraitConfigureRoutes($collection);
        parent::configureRoutes($collection);
    }

    public function getExportFormats(): array
    {
        return ['json', 'xml', 'csv', 'xls'];
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        parent::configureShowFields($show);

        $show->add('category')
            ->add('question')
            ->add('answer')
            ->add('active')
        ;
    }
}
