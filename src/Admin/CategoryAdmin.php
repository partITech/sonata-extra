<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Form\Type\CategorySelectorType;
use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\ClassificationBundle\Admin\ContextAwareAdmin;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @phpstan-extends ContextAwareAdmin<CategoryInterface>
 */
final class CategoryAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;
    protected $classnameLabel = 'Category';
    protected $baseRouteName = 'admin_sonata_extra_category';
    protected $baseRoutePattern = 'sonata_extra_category';

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->configureTraitRoutes($collection);
        $collection->add('tree', 'tree');
        $collection->add('toggle', $this->getRouterIdParameter().'/toggle');
    }

    protected function configure(): void
    {
        $this->configureTrait();
        $this->setTemplates([
            'tree' => '@PartitechSonataExtra/Admin/category/tree.html.twig',
        ]);
    }

    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['constraints'][] = new Valid();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->tab('general')
            ->with('general', ['class' => 'col-md-6'])
                ->add('name')
                ->add('slug')
                ->add('description', TextareaType::class, [
                    'required' => false,
                ]);

        if ($this->hasSubject()) {
            if (null !== $this->getSubject()->getParent() || null === $this->getSubject()->getId()) { // root category cannot have a parent
                $form
                    ->add('parent', CategorySelectorType::class, [
                        'category' => $this->getSubject(),
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'required' => true,
                        'context' => $this->getSubject()->getContext(),
                        'site' => $this->getCurrentSelectedLocal(),
                    ]);
            }
        }

        $position = $this->hasSubject() && null !== $this->getSubject()->getPosition() ? $this->getSubject()->getPosition() : 0;
        $form
            ->end()
            ->with('options', ['class' => 'col-md-6'])
                ->add('enabled', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('position', IntegerType::class, [
                    'required' => false,
                    'data' => $position,
                ])
            ->end()
            ->end();

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
            'btn_add' => 'Select Image', // add image option
            'btn_list' => true,          // list images option
            'btn_delete' => true,        // delete image option
            'btn_catalogue' => 'SonataMediaBundle', // Catalog button
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
            ->add('context', null, [
                'sortable' => 'context.name',
            ])
            ->add('slug')
            ->add('description')
            ->add('enabled', null, ['editable' => true])
            ->add('position')
            ->add('parent', null, [
                'sortable' => 'parent.name',
            ])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'translation_domain' => 'SonataAdminBundle',
                'actions' => [
                    'edit' => [],
                ],
            ]);
    }
}
