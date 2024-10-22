<?php
namespace Partitech\SonataExtra\Block;

use App\Entity\SonataClassificationTag as Tag;
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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class BlogListTagsBlockService extends AbstractBlockService implements EditableBlockService
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private CmsManagerSelectorInterface $cmsSelector;
    private CategoryManagerInterface $categoryManager;
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
        $settings = $blockContext->getSettings();
        $class = $settings['class'];
        $template = $settings['template'];
        $title = $settings['title'];
        $tagWeights = $this->getTagWeights();
        $tagWeights = $this->normalizeWeights($tagWeights);

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'title' => $title,
            'tags' => $tagWeights,
            'class' => $class,
        ], $response);
    }

    private function getTagWeights(): array
    {
        $cms = $this->cmsSelector->retrieve();
        $site = $cms->getCurrentPage()->getSite();

        $subQuery = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a_sub.id)')
            ->from(Article::class, 'a_sub')
            ->join('a_sub.tags', 't_sub')
            ->where('t_sub.id = t.id')
            ->andWhere('a_sub.site = '.$site->getId())
            ->getDQL();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t, (' . $subQuery . ') AS articleCount')
            ->from(Tag::class, 't')
            ->Where('t.site = '.$site->getId())
            ->groupBy('t.id');

        $tagsWithCounts = $qb->getQuery()->getResult();

        $totalArticles = array_sum(array_column($tagsWithCounts, 'articleCount'));

        $tagWeights = [];
        foreach ($tagsWithCounts as $tagWithCount) {

            $tag = $tagWithCount[0];
            $articleCount = $tagWithCount['articleCount'];
            $percentage = ($totalArticles > 0) ? ($articleCount / $totalArticles) * 100 : 0;

            $tagWeights[] = [
                'articleCount'=>$articleCount,
                'tag' => $tag,
                'tag_name' => $tag->getName(),
                'percentage' => $percentage,
            ];
        }

        return $tagWeights;
    }

    private function normalizeWeights(array $tagWeights): array
    {

        $max = max(array_column($tagWeights,'articleCount'));
        foreach ($tagWeights as $key => $value) {
            if ($max > 0) {
                $tagWeights[$key]['percentage'] = ($value['articleCount'] / $max) * 100;
            } else {
                $tagWeights[$key]['percentage'] = 0;
            }
        }

        return $tagWeights;
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'label' => 'Title',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['template', TextType::class, [
                    'label' => 'Max per page',
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
            'tags' => [],
            'max_item' => 25,
            'class' => null,
            'title' => null,
            'template' => '@PartitechSonataExtra/Blocks/blog/list_tag.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Blog : liste de tags ', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-tags',
        ]);
    }
}
