<?php

declare(strict_types=1);

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\Contact;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Contact',
    model_class: Contact::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
final class ContactAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('firstName')
            ->add('lastName')
            ->add('companyName')
            ->add('address')
            ->add('email')
            ->add('phone')
            ->add('additionalInformation')
            ->add('sendMeACopy')
            ->add('createdAt')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('firstName')
            ->add('lastName')
            ->add('companyName')
            ->add('address')
            ->add('email')
            ->add('phone')
            ->add('additionalInformation')
            ->add('sendMeACopy')
            ->add('createdAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('firstName')
            ->add('lastName')
            ->add('companyName')
            ->add('address')
            ->add('email')
            ->add('phone')
            ->add('additionalInformation')
            ->add('sendMeACopy')
            ->add('createdAt')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('firstName')
            ->add('lastName')
            ->add('companyName')
            ->add('address')
            ->add('email')
            ->add('phone')
            ->add('additionalInformation')
            ->add('sendMeACopy')
            ->add('createdAt')
        ;
    }

    public function toString(object $object): string
    {
        return 'Contact';
    }
}
