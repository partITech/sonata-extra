<?php

declare(strict_types=1);

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\Slider;
use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Slider',
    model_class: Slider::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
final class SliderAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('title')
            ->add('description')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('title')
            ->add('description')
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
            // ->add('id')
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('slides', CollectionType::class,
                [
                    'required' => false,
                    'by_reference' => false,
                    'label' => false],
                [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'ordre',
                    'link_parameters' => [
                        'context' => 'default',
                        'provider' => 'sonata.media.provider.file',
                        'hide_context' => true,
                    ],
                    'admin_code' => 'Partitech\SonataExtra\Admin\SliderSlidesAdmin',
                ]
            )
            ->add('ordre')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('title')
            ->add('description')
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->configureTraitRoutes($collection);
        $collection->remove('show');
    }
}
