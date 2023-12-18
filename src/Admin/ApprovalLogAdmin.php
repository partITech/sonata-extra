<?php

namespace Partitech\SonataExtra\Admin;

use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Modifications en attentes',
    model_class: \Partitech\SonataExtra\Entity\AdminActivityLog::class,
    controller: \Partitech\SonataExtra\Controller\Admin\AdminActivityLogController::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class ApprovalLogAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_partitech_sonataextra_approval';

    protected $baseRoutePattern = 'approval';

    protected int $maxPerPage = 50;

    protected ManagerRegistry $registry;

    protected AdminActivityLogRepository $adminActivityLogRepository;

    #[Required]
    public function required(
        ManagerRegistry $registry,
        AdminActivityLogRepository $adminActivityLogRepository
    ): void {
        $this->registry = $registry;
        $this->adminActivityLogRepository = $adminActivityLogRepository;
    }

    public function configure(): void
    {
        $this->setTemplates([
            'list' => '@PartitechSonataExtra/Admin/approval/custom_list.html.twig',
            'show' => '@PartitechSonataExtra/Admin/approval/custom_show.html.twig',
        ]);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PAGE] = 1;
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $rootAlias = current($query->getRootAliases());

        $query->andWhere(
            $query->expr()->eq($rootAlias.'.approval', ':approval_param')
        );
        $query->setParameter('approval_param', 0);  // 1 pour les modifications approuvées

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
                    'approve' => [
                        'template' => '@PartitechSonataExtra/Admin/approval/approve_button.html.twig',
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
            // ... (other fields you want to show)
            ->add('entityChangeLogs', null, [
                'label' => 'Logs de Modification',
                'template' => '@PartitechSonataExtra/Admin/approval/admin_approve_log_details.html.twig',
            ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');  // Supprimer l'action de création
        $collection->remove('delete');  // Supprimer l'action de suppression
        $collection->add('approve', $this->getRouterIdParameter().'/approve');
        $collection->add('purge', 'purge');
    }

    public function getApprovalManyobject($object)
    {
        return $this->adminActivityLogRepository->findByTokenExceptId($object->getToken(), $object->getId());
    }

    /**
     * Used in template file :
     * Partitech/SonataExtraBundle/templates/Admin/admin_approve_log_details.html.twig.
     *
     * @
     */
    public function getApprovalObjectId($data)
    {
        $object = json_decode($data);

        return $object->id;
    }
}
