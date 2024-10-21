<?php

namespace Partitech\SonataExtra\Block;

use App\Entity\SonataClassificationCategory as Category;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Partitech\SonataExtra\Entity\Article;
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
final class BlogArticleCategoryBlockService extends AbstractBlockService implements EditableBlockService
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private CmsManagerSelectorInterface $cmsSelector;
    private CategoryManagerInterface $categoryManager;
    private PaginatorInterface $paginator;

    #[Required]
    public function autowireDependencies(
        Environment                 $twig,
        EntityManagerInterface      $entityManager,
        ParameterBagInterface       $parameterBag,
        CmsManagerSelectorInterface $cmsSelector,
        CategoryManagerInterface    $categoryManager,
        PaginatorInterface          $paginator
    ): void
    {
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

        $cms = $this->cmsSelector->retrieve();
        $site = $cms->getCurrentPage()->getSite();

        $settings = $blockContext->getSettings();
        $class = $settings['class'];
        $template = $settings['template'];
        $max_per_page = $settings['max_per_page'];
        $title = $settings['title'];

        $categories = $settings['categories'];

        $selectedCategoriesIds = [];
        foreach ($categories as $categoryId) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $selectedCategoriesIds = array_merge($selectedCategoriesIds, $this->getCategoryIdsWithChildren($category, $this->entityManager));
            }
        }

        // Deduplicate the IDs
        $selectedCategoriesIds = array_unique($selectedCategoriesIds);

        if (!empty($selectedCategoriesIds)) {
            $articlesQuery = $this->entityManager->createQueryBuilder()
                ->select('a')
                ->from(Article::class, 'a')
                ->leftJoin('a.category', 'c')
                ->where('c.id IN (:categories)')
                ->andWhere('a.site =' . $site->getId())
                ->setParameter('categories', $selectedCategoriesIds)
                ->orderBy('a.publishedAt', 'desc')
                ->getQuery();
        } else {
            $articlesQuery = $this->entityManager->createQueryBuilder()
                ->select('a')
                ->from(Article::class, 'a')
                ->where('a.site = :site')
                ->setParameter('site', $site)
                ->orderBy('a.publishedAt', 'desc')
                ->getQuery();
        }


        $pagination = $this->paginator->paginate(
            $articlesQuery,
            $page,
            $max_per_page
        );

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'pagination' => $pagination,
            'title' => $title,
            'class' => $class,
            'page' => $page,
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        $categoriesChoices = [];
        foreach ($categories as $category) {
            $categoriesChoices[$category->getName()] = $category->getId(); // Assurez-vous que la méthode getName et getId existe dans votre entité Category
        }

        $form->add('settings', ImmutableArrayType::class, [
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
                ['max_per_page', IntegerType::class, [
                    'label' => 'Max per page',
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
            'max_per_page' => 25,
            'class' => null,
            'title' => '',
            'template' => '@PartitechSonataExtra/Blocks/blog/article_category.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(
            title: 'Blog : article de category',
            description: null,
            image: null,
            domain:'SonataBlockBundle',
            options: [
                'class' => 'fa fa-newspaper-o',
            ]
        );
    }

    public function getCategoryIdsWithChildren($category, EntityManagerInterface $entityManager): array
    {
        $categoryIds = [$category->getId()];

        // get recursively children
        foreach ($category->getChildren() as $child) {
            $categoryIds = array_merge($categoryIds, $this->getCategoryIdsWithChildren($child, $entityManager));
        }

        return $categoryIds;
    }
}
