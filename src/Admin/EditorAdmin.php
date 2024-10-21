<?php

namespace Partitech\SonataExtra\Admin;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Menu\ItemInterface;
use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Controller\Admin\EditorController;
use Partitech\SonataExtra\Entity\Editor;
use Partitech\SonataExtra\Enum\EditorStatus;
use Partitech\SonataExtra\Form\Type\GutenbergType;
use Partitech\SonataExtra\Form\Type\MarkdownType;
use Partitech\SonataExtra\Service\GutenbergPatternsService;
use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\ImageProviderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    label: 'Editor',
    model_class: Editor::class,
    controller: EditorController::class,
    calls: [
        ['addChild', [EditorRevisionAdmin::class, 'editor']],
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class EditorAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;
    protected $baseRoutePattern = 'editor';

    private GutenbergPatternsService $GutenbergPatternsService;
    private RouterInterface $router;
    private MediaManager $mediaManager;
    private ImageProviderInterface $providerImage;
    private ParameterBagInterface $parameterBag;
    private string $userClass;
    private string $categoryClass;
    private string $tagClass;


    #[Required]
    public function required(
        GutenbergPatternsService $GutenbergPatternsService,
        RouterInterface $router,
        ParameterBagInterface $parameterBag,
        MediaManager $mediaManager,
        ImageProvider $providerImage
    ): void {
        $this->GutenbergPatternsService = $GutenbergPatternsService;
        $this->router = $router;
        $this->parameterBag = $parameterBag;
        $this->userClass = $parameterBag->get('partitech_sonata_extra.user.class');
        $this->categoryClass = $parameterBag->get('partitech_sonata_extra.category.class');
        $this->tagClass = $parameterBag->get('partitech_sonata_extra.tag.class');
        $this->mediaManager = $mediaManager;
        $this->providerImage = $providerImage;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PAGE] = 1;
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }
    public function configure(): void
    {
        $this->configureTrait();
        $this->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/editor/edit.html.twig',
            'list' => '@PartitechSonataExtra/Admin/editor/list.html.twig',
        ]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->configureTraitRoutes($collection);
        $collection->add('editor-type', $this->getRouterIdParameter().'/editor-type');
    }

    public function configureTabMenu(ItemInterface $menu, string $action, AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $back_menu = $menu->addChild($this->getTranslator()->trans('Retour '),
            $admin->generateMenuUrl($this->getBaseCodeRoute().'.list')
        );
        $back_menu->setExtras([
            'btn_class' => 'btn-info',
            'btn_icon' => 'fa-arrow-left',
        ]);

        $edit_menu = $menu->addChild($this->getTranslator()->trans('Edition '),
            $admin->generateMenuUrl($this->getBaseCodeRoute().'.edit', ['id' => $id])
        );
        $edit_menu->setExtras([
            'btn_class' => ('edit' === $action) ? 'btn-warning' : 'btn-info',
            'btn_icon' => 'fa-edit',
        ]);

        if ($this->isGranted('LIST')) {
            $subject = $admin->getSubject();
            $editorRevisionsCount = null;
            if ($subject) {
                $editorRevisionsCount = count($subject->getRevisions());
            }

            $menu_revision = $menu->addChild('Revisions', $admin->generateMenuUrl(current($this->getChildren())->getCode().'.list', ['id' => $id]));
            $menu_revision->setExtras([
                'sup' => $editorRevisionsCount,
                'btn_class' => !$childAdmin ? 'btn-info' : 'btn-warning',
                'btn_icon' => 'fa-history',
            ]);
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $patterns = $this->GutenbergPatternsService->getPatterns(['partitech']);

        $MediaPatterns = $this->GutenbergPatternsService->getMediaPatterns(
            'default'
        );

        $media = $this->mediaManager->findOneBy([
            'id' => $this->getSubject()->getFeaturedImage()->getId(),
        ]);

        if ($this->hasSubject() && null !== $this->getSubject()->getFeaturedImage()) {
            $mediaUrl = $this->providerImage->generatePublicUrl($media, 'default_small');
        } else {
            $mediaUrl = null;
        }

        $imageHtml = $mediaUrl ? '<img src="'.$mediaUrl.'" alt="'.$media->getName().'" class="img-thumbnail" />' : 'Aucune image sélectionnée';

        if ($this->hasSubject() && null !== $this->getSubject()) {
            $editor = $this->getSubject()->getTypeEditor();
        } else {
            $editor = 'gutenberg';
        }


        $form

            ->tab('Informations Générales') // Pane's name
                ->with('Titre et Url', ['class' => 'col-md-8'])

                    ->add('featured_image', ModelListType::class, [
                        'label' => 'Image mise en avant',
                        'required' => false,
                        'btn_add' => true,
                        'btn_edit' => true,
                        'btn_list' => false,
                        'btn_delete' => false,
                        'class' => $this->parameterBag->get('partitech_sonata_extra.class.media'),
                        'help' => $imageHtml,
                        'help_html' => true,
                    ])
                        ->add('title', null, [
                            'label' => 'Title',
                        ])
                ->end()
                ->with('Informations de Publication', ['class' => 'col-md-4'])
                    ->add('author', EntityType::class, [
                        'class' => $this->userClass,
                        'choice_label' => 'username',
                        'label' => 'Author',
                    ])
                    ->add('publishedAt', DateTimePickerType::class, [
                        'label' => 'Published At',
                        'required' => false,
                    ])

                    ->add('status', ChoiceType::class, [
                        'choices' => EditorStatus::getChoiceValues(),
                    ])

                ->end()

            ->end()
            ->tab('Contenu') //Pane's name
                ->with('Content', ['class' => 'col-md-12']); // section's name
        if ('gutenberg' == $editor) {
            $form->add('content', GutenbergType::class, [
                'label' => 'Content',
                'patterns' => $patterns,
                'media_patterns' => [' Media library', $MediaPatterns],
                'context' => 'default',
            ]);
        }
        if ('ckeditor' == $editor) {
            $form->add('content', CKEditorType::class, [
                'label' => 'Content',
            ]);
        }

        if ('textarea' == $editor) {
            $form->add('content', TextareaType::class, [
                'label' => 'Content',
            ]);
        }

        if ('markdown' == $editor) {
            $form->add('content', MarkdownType::class, [
                'label' => 'Content',
                'attr' => [ 'class' => 'textarea-auto-resize'],
            ]);
        }

        $form->end()
            ->end()

        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title')
            ->add('content')
            ->add('publishedAt')
            ->add('author')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['route' => ['name' => 'edit']])
            ->add('title')
            ->add('publishedAt')
            ->add('author')
            // ->add('content')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
