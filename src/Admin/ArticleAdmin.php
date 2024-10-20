<?php

namespace Partitech\SonataExtra\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Menu\ItemInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Partitech\SonataExtra\Form\Type\GutenbergType;
use Partitech\SonataExtra\Form\Type\MarkdownType;
use Partitech\SonataExtra\Service\GutenbergPatternsService;
use Partitech\SonataExtra\Service\LocaleService;
use Partitech\SonataExtra\Traits\AdminTranslationTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
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
// use Partitech\SonataExtra\Form\Type\CollectionTagsType;
use Symfony\Contracts\Service\Attribute\Required;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;


use Partitech\SonataExtra\Attribute\AsAdmin;

#[AsAdmin(
    manager_type: 'orm',
    label: 'Article',
    model_class: \Partitech\SonataExtra\Entity\Article::class,
    controller: \Partitech\SonataExtra\Controller\Admin\ArticleController::class,
    calls: [
        ['addChild', [\Partitech\SonataExtra\Admin\ArticleRevisionAdmin::class, 'article']],
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class ArticleAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;
    protected $baseRoutePattern = 'article';

    private GutenbergPatternsService $GutenbergPatternsService;
    private RouterInterface $router;
    private MediaManager $mediaManager;
    private ImageProviderInterface $providerImage;
    private ParameterBagInterface $parameterBag;
    private EntityManagerInterface $entityManager;
    private LocaleService $LocaleService;
    private string $userClass;
    private string $categoryClass;
    private string $tagClass;
    private string $seoProposal;
    #[Required]
    public function required(
        GutenbergPatternsService $GutenbergPatternsService,
        RouterInterface $router,
        ParameterBagInterface $parameterBag,
        MediaManager $mediaManager,
        ImageProvider $providerImage,
        EntityManagerInterface $entityManager,
        LocaleService $LocaleService,
    ): void {
        $this->GutenbergPatternsService = $GutenbergPatternsService;
        $this->router = $router;
        $this->parameterBag = $parameterBag;
        $this->userClass = $parameterBag->get('partitech_sonata_extra.user.class');
        $this->categoryClass = $parameterBag->get('partitech_sonata_extra.category.class');
        $this->tagClass = $parameterBag->get('partitech_sonata_extra.tag.class');
        $this->mediaManager = $mediaManager;
        $this->providerImage = $providerImage;
        $this->entityManager = $entityManager;
        $this->LocaleService = $LocaleService;
        $smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');
        $this->seoProposal = $smart_service_conf['seo_proposal_on_article'];

    }

    public function getSeoProposalEnabled(){
        return $this->seoProposal;
    }
    function configureDefaultSortValues(array &$sortValues):void
    {
        $sortValues = array_merge([
            '_sort_order' => 'DESC', // DESC ou ASC
            '_sort_by' => 'id' // Nom de la colonne par laquelle trier
        ], $sortValues);
    }
    public function configure(): void
    {
        $this->configureTrait();
        $this->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/article/edit.html.twig',
            'list' => '@PartitechSonataExtra/Admin/article/list.html.twig',
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
            $articleRevisionsCount = null;
            if ($subject) {
                $articleRevisionsCount = count($subject->getRevisions());
            }

            $menu_revision = $menu->addChild('Revisions', $admin->generateMenuUrl(current($this->getChildren())->getCode().'.list', ['id' => $id]));
            $menu_revision->setExtras([
                'sup' => $articleRevisionsCount,
                'btn_class' => !$childAdmin ? 'btn-info' : 'btn-warning',
                'btn_icon' => 'fa-history',
            ]);
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $entity = $this->getSubject();

        if ($this->hasSubject()){
            $currentSiteId = $entity->getSite()->getId();
        }else{
            $currentSiteId = false;
        }


        $patterns = $this->GutenbergPatternsService->getPatterns(['my-library']);

        $MediaPatterns = $this->GutenbergPatternsService->getMediaPatterns(
            'default'
        );

        if ($this->hasSubject() && null !== $this->getSubject()->getFeaturedImage()) {
            $media = $this->mediaManager->findOneBy([
                'id' => $this->getSubject()->getFeaturedImage()->getId(),
            ]);
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

            ->tab('Informations Générales') // Nom de l'onglet
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
                        ->add('slug', null, [
                            'label' => 'Url',
                            'required' => false,
                        ])
                        ->add('title', null, [
                            'label' => 'Title',
                        ])
                        ->add('excerpt', TextareaType::class, [
                            'label' => 'Excerpt',
                            'required' => false,
                            'attr' => [

                                'style'=>'width: 100%; min-height: 200px'],
                        ])
                ->end()
                ->with('Informations de Publication', ['class' => 'col-md-4'])
                    ->add('isDefault', null, [
                        'label' => 'Default translation',
                    ])
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
                        'choices' => ArticleStatus::getChoiceValues(),
                    ]);


        $categoryRepository = $this->entityManager->getRepository($this->categoryClass);
        $queryCategory = $categoryRepository->createQueryBuilder('c');

        if($currentSiteId){
            $queryCategory->where('c.site = :siteId');
            $queryCategory->setParameter('siteId', $currentSiteId);
        }

        $tagRepository = $this->entityManager->getRepository($this->tagClass);
        $querytag = $tagRepository->createQueryBuilder('t');

        if($currentSiteId){
            $querytag->where('t.site = :siteId');
            $querytag->setParameter('siteId', $currentSiteId);
        }

        if ($entity){
            $entity=$this->LocaleService->fixEntityCategoryTag($entity);
        }

                    $form->add('category', ModelType::class, [
                        'class' => $this->categoryClass,
                        'property' => 'name',
                        'multiple' => true,
                        'btn_add' => 'Ajouter une nouvelle catégorie',
                        'query' => $queryCategory
                    ])

                    ->add('tags', ModelType::class, [
                        'class' => $this->tagClass,
                        'property' => 'name',
                        'multiple' => true,
                        'btn_add' => 'Ajouter un nouveau tag',
                        'query' => $querytag
                    ])

                ->end()

            ->end()
            ->tab('Contenu') // Nom de l'onglet
                ->with('Content', ['class' => 'col-md-12']); // Nom de la section
                    if ('gutenberg' == $editor) {
                        $form->add('content', GutenbergType::class, [
                            'label' => 'Content',
                            'patterns' => $patterns,
                            'media_patterns' => [' Media library', $MediaPatterns],
                            'context' => 'default',
                            'required' => false,
                        ]);
                    }
                    if ('ckeditor' == $editor) {
                        $form->add('content', CKEditorType::class, [
                            'label' => 'Content',
                            'attr' => [ 'class' => 'textarea-auto-resize'],
                            'required' => false,
                        ]);
                    }
            
                    if ('textarea' == $editor) {
                        $form->add('content', TextareaType::class, [
                            'label' => 'Content',
                            'attr' => [ 'class' => 'textarea-auto-resize'],
                            'required' => false,
                        ]);
                    }

                    if ('markdown' == $editor) {
                        $form->add('content', MarkdownType::class, [
                            'label' => 'Content',
                            'attr' => [ 'class' => 'textarea-auto-resize'],
                            'required' => false,
                        ]);
                    }
        $form->end()
            ->end()

            ->tab('SEO')
            ->with('Référencement', ['class' => 'col-md-6'])
            ->add('seo_title', null, [
                'label' => 'SEO Title',
                'required' => false,
            ])
            ->add('seo_description', null, [
                'label' => 'SEO Description',
                'required' => false,
            ])

            ->add('seo_keywords', TextareaType::class, [
                'label' => 'SEO Keywords',
                'required' => false,
                'attr' => [

                    'style'=>'width: 100%; min-height: 200px'],
            ])
            ->end()
            ->with('Open Graph', ['class' => 'col-md-6'])
            ->add('seo_og_title', null, [
                'label' => 'Open Graph Title',
                'required' => false,
            ])
            ->add('seo_og_description', null, [
                'label' => 'Open Graph Description',
                'required' => false,
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
