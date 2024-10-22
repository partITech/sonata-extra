<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Partitech\SonataExtra\Entity\Redirection;
use Partitech\SonataExtra\Controller\Admin\RedirectionAdminController;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Redirections',
    model_class: Redirection::class,
    controller: RedirectionAdminController::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class RedirectionAdmin extends AbstractAdmin
{
    private TokenStorageInterface $tokenStorage;

    #[Required]
    public function required(
        TokenStorageInterface $tokenStorage,
    ): void {
        $this->tokenStorage = $tokenStorage;
    }

    public function prePersist($object): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $object->setUser($user);
    }

    public function preUpdate($object): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $object->setUser($user);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('import', 'import');
        $collection->remove('show');
    }

    public function configureActionButtons(array $buttonList, string $action, object $object = null): array
    {
        $buttonList = parent::configureActionButtons($buttonList, $action, $object);

        if ('list' == $action) {
            $buttonList['import'] = [
                'template' => '@PartitechSonataExtra/Admin/redirection/import_button.html.twig',
            ];
        }

        return $buttonList;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('enabled')
            ->add('source')
            ->add('sourceHost')
            ->add('target')
            ->add('statusCode', ChoiceType::class, [
                'choices' => [
                    '301 Moved Permanently' => 301,
                    '302 Found' => 302,
                    '303 See Other' => 303,
                    '307 Temporary Redirect' => 307,
                    '308 Permanent Redirect' => 308,
                ],
                'label' => 'Status Code',
            ]);
        // ->add('user');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('enabled')
            ->add('source')
            ->add('sourceHost')
            ->add('target')
            ->add('statusCode')
            ->add('user');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'edit']])
            ->add('enabled')
            ->add('source')
            ->add('sourceHost')
            ->add('target')
            ->add('statusCodeLabel', null, [
                'label' => 'Status Code',
            ])
            ->add('user')->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
