<?php

namespace Partitech\SonataExtra\Block;

use App\Entity\SonataClassificationCategory as Category;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class BlogListCategoriesBlockService extends AbstractBlockService implements EditableBlockService
{
    private $entityManager;
    private ParameterBagInterface $parameterBag;
    private CmsManagerSelectorInterface $cmsSelector;
    private $categoryManager;
    private PaginatorInterface $paginator;

    #[Required]
    public function autowireDependencies(
        Environment $twig,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        CmsManagerSelectorInterface $cmsSelector,
        CategoryManagerInterface $categoryManager,
        PaginatorInterface $paginator
    ): void {
        parent::__construct($twig);
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->cmsSelector = $cmsSelector;
        $this->categoryManager = $categoryManager;
        $this->paginator = $paginator;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $page = $blockContext->getBlock()->getSetting('page', 1);
        $category_class = $this->parameterBag->get('partitech_sonata_extra.category.class');

        $cms = $this->cmsSelector->retrieve();
        $site = $cms->getCurrentPage()->getSite();

        $settings = $blockContext->getSettings();
        $class = $settings['class'];
        $template = $settings['template'];
        $max_item = $settings['max_item'];
        $title = $settings['title'];
        $categories = $settings['categories'];

        $selectedCategories = [];
        if(!empty($categories)){
            foreach ($categories as $categoryId) {
                $category = $this->entityManager->getRepository($category_class)->find($categoryId);
                if ($category) {
                    $selectedCategories[] = $category;
                    $childCategoriesIds = $this->getCategoryIdsWithChildren($category, $this->entityManager);
                    if ($childCategoriesIds) {
                        $childCategories = $this->entityManager->createQueryBuilder()
                            ->select('c')
                            ->from($category_class, 'c')
                            ->where('c.id IN (:ids)')
                            ->andWhere('c.site ='.$site->getId())
                            ->setParameter('ids', $childCategoriesIds)
                            ->setMaxResults($max_item)
                            ->getQuery()
                            ->getResult();

                        $selectedCategories = array_merge($selectedCategories, $childCategories);
                    }
                }
            }

        }else{
            $selectedCategories = $this->entityManager->createQueryBuilder()
                ->select('c')
                ->from($category_class, 'c')
                ->Where('c.site ='.$site->getId())
                ->setMaxResults($max_item)
                ->getQuery()
                ->getResult();
        }

        //$selectedCategories = array_unique($selectedCategories, SORT_REGULAR);

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'title' => $title,
            'categories' => $selectedCategories,
            'class' => $class,
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $category_class = $this->parameterBag->get('partitech_sonata_extra.category.class');
        $categories = $this->entityManager->getRepository($category_class)->findAll();

        $categoriesChoices = [];
        foreach ($categories as $category) {
            $categoriesChoices[$category->getName()] = $category->getId(); // Assurez-vous que la méthode getName et getId existe dans votre entité Category
        }

        $formMapper->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'label' => 'Title',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['categories', ChoiceType::class, [
                    'choices' => $categoriesChoices,
                    'multiple' => true,
                    'expanded' => false,
                    'label' => 'Select Categories',
                    'translation_domain' => 'SonataExtraBundle',
                    'required' => false,
                    'data' => $block->getSetting('categories'), // Ici, cela devrait être un tableau d'identifiants de catégorie
                ]],
                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['max_item', IntegerType::class, [
                    'label' => 'Nombe d\'element maximum',
                    'translation_domain' => 'SonataExtraBundle',
                    'constraints' => [
                        new NotBlank(),
                        new Type(['type' => 'integer']),
                    ],
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                ]],
            ],
            'translation_domain' => 'SonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'categories' => [],
            'max_item' => 25,
            'class' => null,
            'title' => null,
            'template' => '@PartitechSonataExtra/Blocks/blog/list_category.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Blog : liste de categorie ', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-tags',
        ]);
    }

    public function getCategoryIdsWithChildren($category, EntityManagerInterface $entityManager)
    {
        $categoryIds = [$category->getId()];
        foreach ($category->getChildren() as $child) {
            $categoryIds = array_merge($categoryIds, $this->getCategoryIdsWithChildren($child, $entityManager));
        }

        return $categoryIds;
    }
}
