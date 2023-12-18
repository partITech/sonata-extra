<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TagAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    protected $classnameLabel = 'Tag';
    protected $baseRouteName = 'admin_sonata_extra_tag';

    protected $baseRoutePattern = 'sonata_extra_tag';

    protected function configureFormFields(FormMapper $form): void
    {
        $form->tab('general')
            ->with('general', ['class' => 'col-md-6'])
            ->add('name')
            ->add('context');
        $form->end();
        $form->with('options', ['class' => 'col-md-6']);
        if ($this->hasSubject() && null !== $this->getSubject()->getId()) {
            $form->add('slug');
        }

        $form->add('enabled', CheckboxType::class, [
            'required' => false,
        ]);
        $form->end();
        $form->end();

        $form->tab('SEO')
            ->with('Référencement', ['class' => 'col-md-6'])
            ->add('seo_title', null, [
                'label' => 'SEO Title',
            ])
            ->add('seo_description', null, [
                'label' => 'SEO Description',
            ])
            ->add('seo_keywords', null, [
                'label' => 'SEO Keywords',
            ])
            ->end()
            ->with('Open Graph', ['class' => 'col-md-6'])
            ->add('seo_og_title', null, [
                'label' => 'Open Graph Title',
            ])
            ->add('seo_og_description', null, [
                'label' => 'Open Graph Description',
            ])
            ->add('seo_og_image', ModelListType::class, [
                'label' => 'Open Graph Image',
                'required' => false,
                'btn_add' => 'Select Image', // Option pour ajouter une image
                'btn_list' => true,          // Option pour lister les images
                'btn_delete' => true,        // Option pour supprimer l'image
                'btn_catalogue' => 'SonataMediaBundle', // Catalogue pour les boutons
            ])
            ->end()
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('name')
            ->add('enabled');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name', null, ['route' => ['name' => 'edit']])
            ->add('slug')
            ->add('context', null, [
                'sortable' => 'context.name',
            ])
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt')
            ->add('updatedAt')
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'translation_domain' => 'SonataAdminBundle',
                'actions' => [
                    'edit' => [],
                ],
            ]);
    }
}
