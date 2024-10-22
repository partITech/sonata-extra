<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Repository\AdminActivityLogRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Partitech\SonataExtra\Entity\AdminActivityLog;
use Partitech\SonataExtra\Controller\Admin\AdminActivityLogController;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Activity Logs',
    model_class: AdminActivityLog::class,
    controller: AdminActivityLogController::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class ActivityLogAdmin extends AbstractAdmin
{
    private AdminActivityLogRepository $adminActivityLogRepository;

    #[Required]
    public function required(AdminActivityLogRepository $adminActivityLogRepository): void
    {
        $this->adminActivityLogRepository = $adminActivityLogRepository;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        // display the first page (default = 1)
        $sortValues[DatagridInterface::PAGE] = 1;

        // reverse order (default = 'ASC')
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';

        // name of the ordered field (default = the model's id field, if any)
        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $rootAlias = current($query->getRootAliases());

        $query->andWhere(
            $query->expr()->eq($rootAlias.'.approval', ':approval_param')
        );
        $query->setParameter('approval_param', 1);  // 1 for approved modifications

        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('date')
            ->add('actionType')
            ->add('resource')
            ->add('user');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('date', null, [
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'date'],
                'sort_parent_association_mappings' => [],
                'sort_by' => ['fieldName' => 'date'],
                'header_style' => 'text-align: center',
                'row_align' => 'center',
            ])
            ->add('actionType')
            ->add('resource')
            ->add('description', FieldDescriptionInterface::TYPE_HTML)
            ->add('user')
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],  // This action will use the 'show' template by default
                    'revert' => [
                        'template' => '@PartitechSonataExtra/Admin/activity_log/revert_button.html.twig',
                    ],
                ],
                'label' => 'Actions',
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('date', null, [
                'label' => 'Date',
            ])
            ->add('actionType', null, [
                'label' => 'Type d\'action',
            ])
            ->add('resource', null, [
                'label' => 'Ressource',
            ])
            ->add('description', FieldDescriptionInterface::TYPE_HTML, [
                'label' => 'Description',
            ])

            ->add('entityChangeLogs', null, [
                'label' => 'Logs de Modification',
                'template' => '@PartitechSonataExtra/Admin/activity_log/admin_activity_log_details.html.twig',
            ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->add('revert', $this->getRouterIdParameter().'/revert');
        $collection->add('revert_item', $this->getRouterIdParameter().'/revert-changelog/{changeLog}');
    }

    public function getApprovalManyobject($object)
    {
        return $this->adminActivityLogRepository->findByTokenExceptId($object->getToken(), $object->getId());
    }
}
