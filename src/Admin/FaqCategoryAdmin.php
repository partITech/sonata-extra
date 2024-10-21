<?php

namespace Partitech\SonataExtra\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\FaqCategory;
use Partitech\SonataExtra\Entity\FaqQuestion;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'FAQs - Catégories',
    model_class: FaqCategory::class,
    calls: [
        ['addChild', ['Partitech\SonataExtra\Admin\FaqQuestionAdmin', 'category']],
        ['setTranslationDomain', ['PartitechSonataExtraBundle']]
    ]
)]
class FaqCategoryAdmin extends AbstractAdmin
{
    use SortableAdminTrait {
        configureRoutes as protected sortableAdminTraitConfigureRoutes;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, ['label' => 'Nom'])
            ->add('active');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('name', null, ['label' => 'Nom']);
        $filter->add('question', ModelFilter::class, [
            'label' => 'Question',
            'field_type' => ModelAutocompleteType::class,
            'field_options' => [
                'property' => 'question',
                'class' => FaqQuestion::class,
            ],
            'association_mapping' => ['fieldName' => 'category'],
        ]);

        $filter->add('questionsFilter', CallbackFilter::class, [
            'label' => 'Question via CallbackFilter',
            /* 'show_filter' => false, */
            'field_type' => TextType::class,
            'callback' => function ($queryBuilder, $alias, $field, $value) {
                $textValue = $value->getValue();
                if ($textValue) {
                    $queryBuilder
                        ->leftJoin(sprintf('%s.questions', $alias), 'q')
                        ->andWhere('q.question LIKE :question')
                        ->setParameter('question', '%' . $textValue . '%');

                }
            },
        ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('manage_questions', 'actions', [
                'label' => 'Gérer les questions',
                'actions' => [
                    'manage_questions' => [
                        'template' => '@PartitechSonataExtra/Admin/faq/manage_questions.html.twig',
                    ],
                ],
                'header_class' => 'col-md-1', // Bootstrap class for the column width
                'row_align' => 'center', // Align the content in the center
            ])
            ->addIdentifier('active', null, ['route' => ['name' => 'edit']])
            ->addIdentifier('name', null, [
                'header_class' => 'col',
                'label' => 'Nom',
                'route' => ['name' => 'edit']
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
                'header_class' => 'col-md-2', // Bootstrap class for the column width
                'row_align' => 'center', // Align the content in the center
            ]);
    }

    public function getExportFormats(): array
    {
        return ['json', 'xml', 'csv', 'xls'];
    }

    /**
     * @throws \JsonException
     */
    protected function configureTabMenu(MenuItemInterface $menu, string $action, AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        if ($this->isGranted('LIST')) {
            $menu->addChild('Gérer les questions', [
                'uri' => $admin->generateUrl('view-questions', ['id' => $id]),
            ]);
        }
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Create child's route ex: /fr/admin/partitech/sonataextra/faqcategory/1/faqquestion/list
        $collection->add(
            'view-questions',
            $this->getRouterIdParameter() . '/' . // récupère l'id de la route parente pour faire le lien sur le child
            $this->getChild(\Partitech\SonataExtra\Admin\FaqQuestionAdmin::class)
                ->generateBaseRoutePattern(true) // génere le petit nom 'faqquestion' de l'url
            . '/list'
        );

        $this->sortableAdminTraitConfigureRoutes($collection);
        parent::configureRoutes($collection);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        parent::configureShowFields($show);

        $show->add('active')
            ->add('name')
            ->add('questions');
    }
}
